<?php

namespace App\Repository;

use App\Entity\ThemeReminderJob;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ThemeReminderJob|null find($id, $lockMode = null, $lockVersion = null)
 * @method ThemeReminderJob|null findOneBy(array $criteria, array $orderBy = null)
 * @method ThemeReminderJob[]    findAll()
 * @method ThemeReminderJob[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ThemeReminderJobRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThemeReminderJob::class);
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
