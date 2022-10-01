<?php

declare(strict_types=1);

namespace App\Kafka\Producer\Context;

use App\Kafka\Producer\Handler\DTO\Batch;
use App\Kafka\SchemaRegistry\SchemaRegistryService;

class CheckProducerContractContext implements ProducerContextInterface
{
    private ProducerContextInterface $context;
    private SchemaRegistryService $schemaRegistryService;

    public function __construct(
        ProducerContextInterface $context,
        SchemaRegistryService $schemaRegistryService,
    ) {
        $this->context = $context;
        $this->schemaRegistryService = $schemaRegistryService;
    }

    public function getMessages(string $topic, int $batchSize): Batch
    {
        $this->schemaRegistryService->createSchema($topic);

        return $this->context->getMessages($topic, $batchSize);
    }
}
