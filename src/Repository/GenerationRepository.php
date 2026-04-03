<?php

namespace App\Repository;

use App\Entity\Generation;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Generation>
 */
class GenerationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Generation::class);
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByUserThisMonth(User $user): int
    {
        $startOfMonth = new \DateTimeImmutable('first day of this month 00:00:00');
        $endOfMonth   = new \DateTimeImmutable('last day of this month 23:59:59');

        return (int) $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->andWhere('g.user = :user')
            ->andWhere('g.createdAt BETWEEN :startOfMonth AND :endOfMonth')
            ->setParameter('user', $user)
            ->setParameter('startOfMonth', $startOfMonth)
            ->setParameter('endOfMonth', $endOfMonth)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByUserToday(User $user): int
    {
        $startOfDay = new \DateTimeImmutable('today 00:00:00');
        $endOfDay   = new \DateTimeImmutable('today 23:59:59');

        return (int) $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->andWhere('g.user = :user')
            ->andWhere('g.createdAt BETWEEN :startOfDay AND :endOfDay')
            ->setParameter('user', $user)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * @return Generation[] Returns an array of Generation objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('g.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Generation
    //    {
    //        return $this->createQueryBuilder('g')
    //            ->andWhere('g.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
