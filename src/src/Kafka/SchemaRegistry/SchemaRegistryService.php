<?php

declare(strict_types=1);

namespace App\Kafka\SchemaRegistry;

use App\Kafka\SchemaRegistry\Handler\SchemaRegistryHandler;

class SchemaRegistryService
{
    public function __construct(
        private readonly SchemaRegistryHandler $schemaRegistryHandler,
        private readonly SchemaPathHelper $schemaPathHelper,
    ) {
    }

    public function createSchema(string $schemaName): int
    {
        $absolutePathToSchema = $this->schemaPathHelper->buildFullPath($schemaName);

        if (!file_exists($absolutePathToSchema)) {
            throw new \RuntimeException("Схема {$schemaName} по пути {$absolutePathToSchema} не найдена!");
        }

        return $this->schemaRegistryHandler->createSchema($schemaName, $absolutePathToSchema);
    }
}
