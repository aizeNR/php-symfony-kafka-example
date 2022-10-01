<?php

declare(strict_types=1);

namespace App\Kafka\SchemaRegistry\Client;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SchemaRegistryHttpClient
{
    private const CREATE_SCHEMA_URL = '/subjects/%s/versions';

    private HttpClientInterface $client;
    private LoggerInterface $logger;

    public function __construct(
        HttpClientInterface $schemaRegistryClient,
        LoggerInterface $logger,
    ) {
        $this->client = $schemaRegistryClient;
        $this->logger = $logger;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     */
    public function createSchema(string $schema, string $schemaName): int
    {
        $response = $this->callMethod(sprintf(self::CREATE_SCHEMA_URL, $schemaName), Request::METHOD_POST, [
            'body' => json_encode([
                'schema' => $schema,
                'schemaType' => 'PROTOBUF',
            ], JSON_THROW_ON_ERROR),
            'headers' => [
                'Content-Type' => 'application/vnd.schemaregistry.v1+json',
            ],
        ]);

        return $response['id'] ?? throw new \RuntimeException('SchemaRegistryHttpClient. Не удалось зарегистрировать схему!');
    }

    /**
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws \JsonException
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     */
    private function callMethod(string $url, string $method, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $url, $options);

            $content = $response->getContent();
        } catch (Exception $exception) {
            $this->logger->error("SchemaRegistryHttpClient return error: {$exception->getMessage()}", [
                'content' => isset($response) ? $response->getContent(false) : null,
            ]);

            throw $exception;
        }

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
