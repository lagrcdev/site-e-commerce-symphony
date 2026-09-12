<?php

namespace App\Repository;

use App\Entity\SweatShirt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SweatShirt>
 */
class SweatShirtRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SweatShirt::class);
    }

    /**
     * @return SweatShirt[]
     */
    public function findByPriceRange(float $min, float $max): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.price >= :min')
            ->andWhere('s.price <= :max')
            ->setParameter('min', $min)
            ->setParameter('max', $max)
            ->getQuery()
            ->getResult()
        ;
    }
}
