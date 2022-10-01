<?php

declare(strict_types=1);

namespace App\Kafka\Command;

use App\Kafka\Producer\Context\ProducerContextInterface;
use Psr\Log\LoggerInterface;
use RdKafka\Conf;
use RdKafka\Producer;
use RdKafka\ProducerTopic;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProducerRunCommand extends Command
{
    protected static $defaultName = 'kafka:producer:run';
    protected static $defaultDescription = 'Run producer';

    private const FLUSH_TIMEOUT = 1000 * 60 * 2; // 2 минуты
    private const RETRY_FLUSH_COUNT = 10;

    public function __construct(
        private readonly string $kafkaBrokers,
        private readonly ProducerContextInterface $context,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription)
            ->addArgument('topic', InputArgument::REQUIRED, 'Topic')
            ->addOption('batch', 'b', InputOption::VALUE_OPTIONAL, 'Batch size to push message', 10);
    }

    /**
     * @psalm-suppress InvalidReturnType
     *
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->validate($input);

        $io = new SymfonyStyle($input, $output);

        $io->info('Конфигурируем producer-а.');

        $topicName = $input->getArgument('topic');
        $batchSize = (int)$input->getOption('batch');

        $conf = new Conf();
        $conf->set('metadata.broker.list', $this->kafkaBrokers);
        $conf->setErrorCb(function ($kafka, $err, $reason) {
            $this->logger->error(
                sprintf('ProducerRunCommand. Kafka error: %s (reason: %s)', rd_kafka_err2str($err), $reason)
            );
        });

        $producer = new Producer();
        $producer->addBrokers($this->kafkaBrokers);

        /** @var ProducerTopic $topic */
        $topic = $producer->newTopic($topicName);

        $io->info('Запускаем процесс...');

        while (true) {
            $response = $this->context->getMessages($topicName, $batchSize);

            $io->info(sprintf('Получено %d сообщений', count($response->getMessages())));

            if (empty($response->getMessages())) {
                sleep(60);
                continue;
            }

            $onSuccessCallback = $response->getOnSuccessCallback();

            try {
                foreach ($response->getMessages() as $message) {
                    $topic->produce(RD_KAFKA_PARTITION_UA, 0, $message);
                    $producer->poll(0);
                }

                $io->info('Выполняем FLUSH!');

                $result = null;
                for ($i = 0; $i < self::RETRY_FLUSH_COUNT; ++$i) {
                    $result = $producer->flush(self::FLUSH_TIMEOUT);
                    if (RD_KAFKA_RESP_ERR_NO_ERROR === $result) {
                        break;
                    }
                }

                if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
                    $this->logger->error("ProducerRunCommand. Не удалось выполнить FLUSH! Ошибка: {$result}");
                    throw new \RuntimeException('Was unable to flush, messages might be lost!');
                }

                $io->info('Сообщения отправлены в кафку!');

                if (!is_null($onSuccessCallback)) {
                    $onSuccessCallback();
                }
            } catch (\Exception $e) {
                $this->logger->error("ProducerRunCommand. Ошибка: {$e->getMessage()}");

                throw $e;
            }
        }
    }

    private function validate(InputInterface $input): void
    {
        $consumer = $input->getArgument('topic');
        if (empty($consumer)) {
            throw new \InvalidArgumentException('Topic является обязательным параметром!');
        }
    }
}
