<?php

declare(strict_types=1);

namespace App\Kafka\Producer\Handler;

use App\DLQ\V1\DeadLetter;
use App\Kafka\Producer\Handler\DTO\Batch;
use App\Kafka\Repository\Event\EventRepositoryInterface;

class DeadLetterProducer implements ProducerInterface
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function getMessage(string $topic, int $batchSize): Batch
    {
        $events = $this->eventRepository->findByTopic($topic, $batchSize);

        if (empty($events)) {
            return new Batch([], null);
        }

        $messages = [];
        foreach ($events as $event) {
            $deadLetter = new DeadLetter();
            $deadLetter->mergeFromJsonString($event->getPayload());

            $messages[] = $deadLetter->serializeToString();
        }

        return new Batch(
            $messages,
            function () use ($events) {
                $this->eventRepository->deleteEvents($events);
            }
        );
    }
}
