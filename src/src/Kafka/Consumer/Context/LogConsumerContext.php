<?php

declare(strict_types=1);

namespace App\Kafka\Consumer\Context;

use App\Kafka\Consumer\ConsumeStatus;
use Psr\Log\LoggerInterface;
use RdKafka\Message;

class LogConsumerContext implements ConsumerContextInterface
{
    private ConsumerContextInterface $context;
    private LoggerInterface $logger;

    public function __construct(ConsumerContextInterface $context, LoggerInterface $logger)
    {
        $this->context = $context;
        $this->logger = $logger;
    }

    /**
     * @throws \Exception
     */
    public function process(Message $message): ConsumeStatus
    {
        $optionsToDebug = [
            'offset' => $message->offset,
            'errorCode' => $message->err,
            'topic' => $message->topic_name,
            'partition' => $message->partition,
        ];

        $this->logger->debug('ConsumerLogContext. Get Message', $optionsToDebug);

        try {
            $status = $this->context->process($message);
        } catch (\Exception $e) {
            $this->logger->error(
                'ConsumerLogContext. Error',
                array_merge(
                    $optionsToDebug,
                    [
                        'exception' => $e->getMessage(),
                    ]
                )
            );

            throw $e;
        }

        $level = $this->getLogLevelByStatus($status);

        $this->logger->{$level}('ConsumerLogContext. Response', array_merge($optionsToDebug, [
            'status' => $status,
        ]));

        return $status;
    }

    private function getLogLevelByStatus(ConsumeStatus $status): string
    {
        return match ($status) {
            ConsumeStatus::UnrecognizedError => 'error',
            default => 'debug',
        };
    }
}
