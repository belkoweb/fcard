<?php

namespace App\Repository;

use App\Entity\LastConnection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method LastConnection|null find($id, $lockMode = null, $lockVersion = null)
 * @method LastConnection|null findOneBy(array $criteria, array $orderBy = null)
 * @method LastConnection[]    findAll()
 * @method LastConnection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LastConnectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LastConnection::class);
    }

    // /**
    //  * @return LastConnection[] Returns an array of LastConnection objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('l.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?LastConnection
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
