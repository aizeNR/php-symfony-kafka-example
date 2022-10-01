<?php

declare(strict_types=1);

namespace App\Kafka\Consumer\Handler;

use App\Kafka\Consumer\ConsumeStatus;
use RdKafka\Message;
use System\Item\V1\ItemUpdated;

class ItemUpdatedHandler implements HandlerInterface
{
    /**
     * @throws \Exception
     */
    public function process(Message $message): ConsumeStatus
    {
//        throw new \Exception('Ooops!');
//        $itemUpdated = new ItemUpdated();
//
//        $itemUpdated->mergeFromString($message->payload);

        return ConsumeStatus::Success;
    }
}
