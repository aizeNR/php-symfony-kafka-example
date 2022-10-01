<?php

declare(strict_types=1);

namespace App\Kafka\Producer\Context;

use App\Kafka\Producer\Handler\DTO\Batch;
use App\Kafka\Producer\Handler\ProducerFactory;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class StrategyProducerContext implements ProducerContextInterface
{
    public function __construct(
        private readonly ProducerFactory $producerFactory
    ) {
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getMessages(string $topic, int $batchSize): Batch
    {
        return $this->producerFactory
            ->getProducerByTopic($topic)
            ->getMessage($topic, $batchSize);
    }
}
