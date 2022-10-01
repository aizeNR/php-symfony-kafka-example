<?php

namespace App\Kafka\Consumer\Context;

use App\Kafka\Consumer\ConsumeStatus;
use RdKafka\Message;

interface ConsumerContextInterface
{
    public function process(Message $message): ConsumeStatus;
}
