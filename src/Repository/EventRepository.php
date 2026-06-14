<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\DBAL\Connection;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    private Connection $connection;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
        $this->connection = $registry->getConnection();
    }

        /**
     * SELECT - Requete SQL brute
     */
    public function findAllRaw(): array
    {
        $sql = "
            SELECT id, title, description, status,
                   created_at AS createdAt,
                   updated_at AS updatedAt,
                   due_date AS dueDate
            FROM event
            ORDER BY created_at DESC
        ";

        $result = $this->connection->executeQuery($sql);
        return $result->fetchAllAssociative();
    }

    /**
     * INSERT - Requete SQL brute
     */
    public function insertRaw(string $title, ?string $description, string $status): int
    {
        $sql = "
            INSERT INTO event (title, description, status, created_at)
            VALUES (:title, :description, :status, :createdAt)
        ";

        $this->connection->executeStatement($sql, [
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'createdAt' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        return (int) $this->connection->lastInsertId();
    }

    /**
     * UPDATE - Requete SQL brute
     */
    public function updateRaw(int $id, string $title, ?string $description, string $status): int
    {
        $sql = "
            UPDATE event
            SET title = :title,
                description = :description,
                status = :status,
                updated_at = :updatedAt
            WHERE id = :id
        ";

        return $this->connection->executeStatement($sql, [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'updatedAt' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * DELETE - Requete SQL brute
     */
    public function deleteRaw(int $id): int
    {
        $sql = "DELETE FROM event WHERE id = :id";
        return $this->connection->executeStatement($sql, ['id' => $id]);
    }

    // Trouve toutes les event par dates

    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('t')
        ->orderBy('t.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
    }

    // trouve les taches par status

    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('t')
        ->andWhere('t.status = :status')
        ->setParameter('status', $status)
        ->orderBy('t.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
    }
    public function save(Event $event, bool $flush = true): void
    {
        $this->getEntityManager()->persist($event);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Event $event, bool $flush = true): void
    {
        $this->getEntityManager()->remove($event);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

}
