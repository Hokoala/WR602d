<?php

namespace App\Repository;

use App\Entity\Tool;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tool>
 */
class ToolRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tool::class);
    }

    public function findByNameKeyword(string $keyword): ?Tool
    {
        return $this->createQueryBuilder('t')
            ->where('LOWER(t.name) LIKE LOWER(:keyword)')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
