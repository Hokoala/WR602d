<?php

namespace App\Repository;

use App\Entity\PdfQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PdfQueue>
 */
class PdfQueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PdfQueue::class);
    }

    /** @return PdfQueue[] */
    public function findPending(int $limit = 5): array
    {
        return $this->createQueryBuilder('q')
            ->where('q.status = :status')
            ->setParameter('status', 'pending')
            ->orderBy('q.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countPendingByUser(\App\Entity\User $user): int
    {
        return (int) $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->where('q.status = :status')
            ->andWhere('q.user = :user')
            ->setParameter('status', 'pending')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
