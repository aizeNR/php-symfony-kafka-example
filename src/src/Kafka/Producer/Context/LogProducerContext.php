<?php

declare(strict_types=1);

namespace App\Kafka\Producer\Context;

use App\Kafka\Producer\Handler\DTO\Batch;
use Psr\Log\LoggerInterface;

class LogProducerContext implements ProducerContextInterface
{
    public function __construct(
        private readonly ProducerContextInterface $context,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function getMessages(string $topic, int $batchSize): Batch
    {
        $optionsToDebug = [
            'topic' => $topic,
            'batchSize' => $batchSize,
        ];

        $this->logger->debug('LogProducerContext. Получение сообщений для отправки в кафку.', $optionsToDebug);

        try {
            $response = $this->context->getMessages($topic, $batchSize);
        } catch (\Exception $e) {
            $this->logger->error("LogProducerContext. Error: {$e->getMessage()}", $optionsToDebug);

            throw $e;
        }

        $this->logger->debug('LogProducerContext. Сообщения получены', array_merge($optionsToDebug, [
            'messageCount' => count($response->getMessages()),
        ]));

        return $response;
    }
}
