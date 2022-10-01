<?php

declare(strict_types=1);

namespace App\Kafka\Consumer\Context;

use Exception;
use App\Kafka\Consumer\ConsumeStatus;
use App\Kafka\Consumer\Handler\HandlerFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use RdKafka\Message;

class StrategyConsumerContext implements ConsumerContextInterface
{
    private LoggerInterface $logger;
    private HandlerFactory $factory;

    public function __construct(HandlerFactory $factory, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->factory = $factory;
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(Message $message): ConsumeStatus
    {
        return match ($message->err) {
            RD_KAFKA_RESP_ERR_NO_ERROR => $this->handle($message),
            RD_KAFKA_RESP_ERR__PARTITION_EOF => ConsumeStatus::Kafka_PartitionEOF,
            RD_KAFKA_RESP_ERR__TIMED_OUT => ConsumeStatus::Kafka_Timeout,
            default => ConsumeStatus::UnrecognizedError,
        };
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    private function handle(Message $message): ConsumeStatus
    {
        $handler = $this->factory->getHandlerByTopic($message->topic_name);

        try {
            return $handler->process($message);
        } catch (Exception $exception) {
            $this->logger->error(sprintf('%s. Не удалось обработать сообшение!', $handler::class), [
                'exception' => $exception->getMessage(),
                'offset' => $message->offset,
                'topic' => $message->topic_name,
                'partition' => $message->partition,
            ]);

            throw $exception;
        }
    }
}
