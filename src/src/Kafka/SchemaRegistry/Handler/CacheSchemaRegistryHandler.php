<?php

declare(strict_types=1);

namespace App\Kafka\SchemaRegistry\Handler;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

class CacheSchemaRegistryHandler implements SchemaRegistryHandler
{
    private const HASH_SCHEMA_TTL = 60 * 60; // 1 час

    public function __construct(
        private readonly SchemaRegistryHandler $handler,
        private readonly CacheInterface $cache
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function createSchema(string $schemaName, string $pathToSchema): int
    {
        $hash = $this->getHash($pathToSchema);

        if ($id = $this->cache->get($hash)) {
            return (int)$id;
        }

        $id = $this->handler->createSchema($schemaName, $pathToSchema);

        $this->cache->set($hash, $id, self::HASH_SCHEMA_TTL);

        return $id;
    }

    private function getHash(string $pathToSchema): string
    {
        $hash = md5_file($pathToSchema);

        if (!$hash) {
            throw new \RuntimeException("Не удалось получить хеш файла по пути {$pathToSchema}");
        }

        return $hash;
    }
}
