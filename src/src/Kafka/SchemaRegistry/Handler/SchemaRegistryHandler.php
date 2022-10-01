<?php

namespace App\Kafka\SchemaRegistry\Handler;

interface SchemaRegistryHandler
{
    public function createSchema(string $schemaName, string $pathToSchema): int;
}
