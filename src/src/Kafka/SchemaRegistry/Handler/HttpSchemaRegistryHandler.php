<?php

declare(strict_types=1);

namespace App\Kafka\SchemaRegistry\Handler;

use App\Kafka\SchemaRegistry\Client\SchemaRegistryHttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class HttpSchemaRegistryHandler implements SchemaRegistryHandler
{
    public function __construct(private readonly SchemaRegistryHttpClient $client)
    {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    public function createSchema(string $schemaName, string $pathToSchema): int
    {
        $schema = $this->getSchema($pathToSchema);

        return $this->client->createSchema($schema, $schemaName);
    }

    private function getSchema(string $pathToSchema): string
    {
        $schema = file_get_contents($pathToSchema);

        if (!$schema) {
            throw new \RuntimeException("Не удалось прочитать файл {$pathToSchema}");
        }

        return $schema;
    }
}
