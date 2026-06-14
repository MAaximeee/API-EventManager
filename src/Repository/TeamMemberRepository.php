<?php

namespace App\Repository;

use App\Entity\TeamMember;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TeamMemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamMember::class);
    }

    public function save(TeamMember $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TeamMember $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByTeam(Team $team): array
    {
        return $this->findBy(['team' => $team]);
    }

    public function countByTeam(Team $team): int
    {
        return $this->count(['team' => $team]);
    }

    public function findUserTeamInEvent(User $user, Event $event): ?TeamMember
    {
        return $this->createQueryBuilder('tm')
            ->innerJoin('tm.team', 't')
            ->where('tm.user = :user')
            ->andWhere('t.event = :event')
            ->setParameter('user', $user)
            ->setParameter('event', $event)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCaptainByTeam(Team $team): ?TeamMember
    {
        return $this->findOneBy([
            'team' => $team,
            'role' => TeamMember::ROLE_CAPTAIN
        ]);
    }
}