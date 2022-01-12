<?php

namespace App\Repository;

use App\Entity\GoalReminder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method GoalReminder|null find($id, $lockMode = null, $lockVersion = null)
 * @method GoalReminder|null findOneBy(array $criteria, array $orderBy = null)
 * @method GoalReminder[]    findAll()
 * @method GoalReminder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GoalReminderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GoalReminder::class);
    }

    // /**
    //  * @return GoalReminder[] Returns an array of GoalReminder objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('g.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?GoalReminder
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
