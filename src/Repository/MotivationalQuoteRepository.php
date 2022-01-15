<?php

namespace App\Repository;

use App\Entity\MotivationalQuote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MotivationalQuote|null find($id, $lockMode = null, $lockVersion = null)
 * @method MotivationalQuote|null findOneBy(array $criteria, array $orderBy = null)
 * @method MotivationalQuote[]    findAll()
 * @method MotivationalQuote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MotivationalQuoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MotivationalQuote::class);
    }

    // /**
    //  * @return MotivationalQuote[] Returns an array of MotivationalQuote objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?MotivationalQuote
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
