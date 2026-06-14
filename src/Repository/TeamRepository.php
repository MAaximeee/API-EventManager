<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function save(Team $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Team $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByEvent(Event $event): array
    {
        return $this->findBy(['event' => $event]);
    }

    public function countByEvent(Event $event): int
    {
        return $this->count(['event' => $event]);
    }
}