<?php

declare(strict_types=1);

namespace App\Kafka\SchemaRegistry;

class SchemaPathHelper
{
    public function __construct(
        private readonly string $kafkaPathToContracts,
    ) {
    }

    public function buildFullPath(string $schemaName): string
    {
        $path = implode(DIRECTORY_SEPARATOR, explode('.', $schemaName));

        return getcwd() . $this->kafkaPathToContracts . $path . DIRECTORY_SEPARATOR . $schemaName . '.proto';
    }
}
