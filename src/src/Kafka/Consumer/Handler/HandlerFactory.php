<?php

namespace App\Kafka\Consumer\Handler;

use App\Kafka\Enum\Topic;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class HandlerFactory implements ServiceSubscriberInterface
{
    public function __construct(private readonly ContainerInterface $locator)
    {
    }

    public static function getSubscribedServices(): array
    {
        return [
            Topic::SYSTEM_ITEM_V1 => ItemUpdatedHandler::class,
            Topic::PARTNERS_DLQ_V1 => DeadLetterHandler::class,
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getHandlerByTopic(string $topic): HandlerInterface
    {
        if (!$this->locator->has($topic)) {
            throw new \InvalidArgumentException("Не найден хендлер для топика {$topic}");
        }

        return $this->locator->get($topic);
    }
}
