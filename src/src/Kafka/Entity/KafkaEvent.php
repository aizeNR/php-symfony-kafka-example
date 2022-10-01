<?php

declare(strict_types=1);

namespace App\Kafka\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Kafka\Repository\Event\EventRepository;

/**
 * @ORM\Entity(repositoryClass=EventRepository::class)
 * @ORM\Table(name="kafka_event")
 */
class KafkaEvent
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="text")
     */
    private string $topic;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private ?string $payload;

    /**
     * @ORM\Column(type="datetime_immutable", options={"default": "CURRENT_TIMESTAMP"})
     */
    private \DateTimeImmutable $createdAt;

    public function __construct(string $topic, ?string $payload)
    {
        $this->topic = $topic;
        $this->payload = $payload;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getPayload(): ?string
    {
        return $this->payload;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
