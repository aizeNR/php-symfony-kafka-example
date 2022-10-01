<?php

namespace App\Kafka\Producer\Context;

use App\Kafka\Producer\Handler\DTO\Batch;

interface ProducerContextInterface
{
    public function getMessages(string $topic, int $batchSize): Batch;
}
