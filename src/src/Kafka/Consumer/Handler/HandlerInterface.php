<?php

namespace App\Kafka\Consumer\Handler;

use App\Kafka\Consumer\ConsumeStatus;
use RdKafka\Message;

interface HandlerInterface
{
    public function process(Message $message): ConsumeStatus;
}
