<?php

declare(strict_types=1);

namespace App\Kafka\Repository\Event;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Kafka\Entity\KafkaEvent;

/**
 * @psalm-suppress LessSpecificImplementedReturnType
 *
 * @method KafkaEvent|null find($id, $lockMode = null, $lockVersion = null)
 * @method KafkaEvent|null findOneBy(array $criteria, array $orderBy = null)
 * @method KafkaEvent[]    findAll()
 * @method KafkaEvent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository implements EventRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, KafkaEvent::class);
    }

    public function saveEvent(KafkaEvent $event): void
    {
        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();
    }

    public function findByTopic(string $topic, int $batchSize): array
    {
        return $this->findBy([
            'topic' => $topic,
        ], [
            'id' => 'asc',
        ], $batchSize);
    }

    public function deleteEvents(array $events): void
    {
        foreach ($events as $event) {
            $this->getEntityManager()->remove($event);
        }

        $this->getEntityManager()->flush();
    }
}
