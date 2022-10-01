<?php

declare(strict_types=1);

namespace App\Kafka\Producer\Handler;

use App\Kafka\Enum\Topic;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class ProducerFactory implements ServiceSubscriberInterface
{
    public function __construct(private readonly ContainerInterface $locator)
    {
    }

    public static function getSubscribedServices(): array
    {
        return [
            Topic::PARTNERS_DLQ_V1 => DeadLetterProducer::class,
        ];
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getProducerByTopic(string $topic): ProducerInterface
    {
        if (!$this->locator->has($topic)) {
            throw new \InvalidArgumentException("Не найден producer для топика {$topic}");
        }

        return $this->locator->get($topic);
    }
}
