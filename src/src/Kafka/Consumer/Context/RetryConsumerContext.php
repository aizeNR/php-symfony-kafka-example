<?php

namespace App\Kafka\Consumer\Context;

use App\DLQ\V1\DeadLetter;
use App\Kafka\Consumer\ConsumeStatus;
use App\Kafka\Entity\KafkaEvent;
use App\Kafka\Enum\Topic;
use App\Kafka\Repository\Event\EventRepositoryInterface;
use Psr\Log\LoggerInterface;
use RdKafka\Message;

class RetryConsumerContext implements ConsumerContextInterface
{
    public function __construct(
        private readonly ConsumerContextInterface $context,
        private readonly LoggerInterface $logger,
        private readonly EventRepositoryInterface $eventRepository,
    ) {
    }

    private const DEFAULT_RETRY_COUNT = 3;

    private const RETRY_MAP_SETTING = [
        Topic::SYSTEM_ITEM_V1 => [
            'retryCount' => self::DEFAULT_RETRY_COUNT,
            'moveToDLQ' => true,
        ],
        Topic::PARTNERS_DLQ_V1 => [
            'moveToDLQ' => false,
        ],
    ];

    public function process(Message $message): ConsumeStatus
    {
        $settingMap = self::RETRY_MAP_SETTING[$message->topic_name] ?? [];

        $retryCount = $settingMap['retryCount'] ?? self::DEFAULT_RETRY_COUNT;
        $moveToDLQ = $settingMap['moveToDLQ'] ?? true;
        $debugOptions = [
            'topic' => $message->topic_name,
            'offset' => $message->offset,
            'partition' => $message->partition,
            'setting' => $settingMap,
        ];

        $this->logger->debug('RetryConsumerContext', $debugOptions);

        do {
            try {
                return $this->context->process($message);
            } catch (\Exception $exception) {
                --$retryCount;

                $this->logger->error("RetryConsumerContext. Error {$exception->getMessage()}", array_merge($debugOptions, [
                    'message' => $exception->getMessage(),
                    'retryCount' => $retryCount,
                ]));
            }
        } while ($retryCount > 0);

        $this->logger->info('RetryConsumerContext. Попытки кончились.', $debugOptions);

        if (!$moveToDLQ) {
            return ConsumeStatus::Success;
        }

        $deadLetter = new DeadLetter([
            'topic' => $message->topic_name,
            'payload' => $message->payload,
        ]);

        $this->eventRepository->saveEvent(new KafkaEvent(
            Topic::PARTNERS_DLQ_V1,
            $deadLetter->serializeToJsonString()
        ));

        return ConsumeStatus::MoveToDeadLetterTopic;
    }
}
