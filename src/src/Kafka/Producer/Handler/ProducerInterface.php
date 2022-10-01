<?php

namespace App\Kafka\Producer\Handler;

use App\Kafka\Producer\Handler\DTO\Batch;

interface ProducerInterface
{
    public function getMessage(string $topic, int $batchSize): Batch;
}
