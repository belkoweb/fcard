<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\DailyCount;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method DailyCount|null find($id, $lockMode = null, $lockVersion = null)
 * @method DailyCount|null findOneBy(array $criteria, array $orderBy = null)
 * @method DailyCount[]    findAll()
 * @method DailyCount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DailyCountRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyCount::class);
    }

    // /**
    //  * @return DailyCount[] Returns an array of DailyCount objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DailyCount
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
