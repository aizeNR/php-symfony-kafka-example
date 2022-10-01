<?php

declare(strict_types=1);

namespace App\Kafka\Command;

use App\Kafka\Consumer\ConsumeStatus;
use App\Kafka\Consumer\Context\ConsumerContextInterface;
use Psr\Log\LoggerInterface;
use RdKafka\Conf;
use RdKafka\KafkaConsumer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConsumerRunCommand extends Command
{
    protected static $defaultName = 'kafka:consumer:run';
    protected static $defaultDescription = 'Run consumer';

    private const CONSUME_TIMEOUT = 1000 * 60 * 2; // 2 минуты

    public function __construct(
        private readonly string $kafkaBrokers,
        private readonly ConsumerContextInterface $context,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription)
            ->addArgument('consumer', InputArgument::REQUIRED, 'Consumer group ID')
            ->addOption('topic', 't', InputOption::VALUE_REQUIRED, 'Topic');
    }

    /**
     * @psalm-suppress InvalidReturnType
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->validate($input);

        $consumerGroup = $input->getArgument('consumer');
        $topic = $input->getOption('topic');
        $io = new SymfonyStyle($input, $output);

        $io->info('Конфигурируем consumer-a.');

        $conf = new Conf();
        $conf->set('metadata.broker.list', $this->kafkaBrokers);
        $conf->set('group.id', $consumerGroup);
        $conf->set('enable.auto.commit', 'false');
        $conf->set('auto.offset.reset', 'earliest');
        $conf->setErrorCb(function ($kafka, $err, $reason) {
            $this->logger->error(
                sprintf('ConsumerRunCommand. Kafka error: %s (reason: %s)', rd_kafka_err2str($err), $reason)
            );
        });

        $consumer = new KafkaConsumer($conf);

        $consumer->subscribe([$topic]);

        $io->info('Запускаем процесс...');

        while (true) {
            $io->info('Получем сообщение..');

            $message = $consumer->consume(self::CONSUME_TIMEOUT);

            $io->info('Сообщение получено! Начинаем обработку.');

            $status = $this->context->process($message);

            $io->info("Обработка закончена. Статус обработки: {$status->toString()}");

            if (in_array($status, ConsumeStatus::STATUS_FOR_COMMIT, true)) {
                $consumer->commit($message);
            } elseif (in_array($status, ConsumeStatus::STATUS_FOR_SLEEP, true)) {
                sleep(15);
            }
        }
    }

    private function validate(InputInterface $input): void
    {
        $consumer = $input->getArgument('consumer');
        if (empty($consumer)) {
            throw new \InvalidArgumentException('Consumer группа является обязательным параметром!');
        }

        $topicName = $input->getOption('topic');
        if (empty($topicName)) {
            throw new \InvalidArgumentException('Topic является обязательным параметром!');
        }
    }
}
