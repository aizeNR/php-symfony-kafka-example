<?php

namespace App\Kafka\Repository\Event;

use App\Kafka\Entity\KafkaEvent;

interface EventRepositoryInterface
{
    /**
     * @param KafkaEvent[] $events
     */
    public function deleteEvents(array $events): void;

    public function saveEvent(KafkaEvent $event): void;

    /**
     * @return KafkaEvent[]
     */
    public function findByTopic(string $topic, int $batchSize): array;
}
