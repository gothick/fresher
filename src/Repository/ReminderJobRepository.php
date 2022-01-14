<?php

namespace App\Repository;

use App\Entity\ReminderJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ReminderJob|null find($id, $lockMode = null, $lockVersion = null)
 * @method ReminderJob|null findOneBy(array $criteria, array $orderBy = null)
 * @method ReminderJob[]    findAll()
 * @method ReminderJob[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ReminderJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReminderJob::class);
    }

    // /**
    //  * @return ReminderJob[] Returns an array of ReminderJob objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ReminderJob
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
