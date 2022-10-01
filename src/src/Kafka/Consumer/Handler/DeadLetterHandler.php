<?php

declare(strict_types=1);

namespace App\Kafka\Consumer\Handler;

use App\DLQ\V1\DeadLetter;
use App\Kafka\Consumer\ConsumeStatus;
use RdKafka\Message;
use System\Item\V1\ItemUpdated;

class DeadLetterHandler implements HandlerInterface
{
    public function process(Message $message): ConsumeStatus
    {
        $deadLetter = new DeadLetter();
        $deadLetter->mergeFromString($message->payload);

        $itemUpdated = new ItemUpdated();
        $itemUpdated->mergeFromString($deadLetter->getPayload());

        return ConsumeStatus::Success;
    }
}
