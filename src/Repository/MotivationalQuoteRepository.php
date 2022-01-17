<?php

namespace App\Repository;

use App\Entity\MotivationalQuote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NativeQuery;
use Exception;

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

    /**
     * @return mixed
     */
    public function getRandomQuote()
    {
        // TODO: Can probably use a Doctrine extenion to make this even more
        // cross-platform with less code; see https://stackoverflow.com/a/40959512/300836
        $platform = $this->getEntityManager()->getConnection()->getDatabasePlatform();

        if ($platform instanceof PostgreSQLPlatform) {
            $randomFunc = 'random()';
        } elseif ($platform instanceof MySQLPlatform) {
            $randomFunc = 'rand()';
        } else {
            throw new Exception('getRandomQuote only copes with MySQL and Postgres so far.');
        }

        $table = $this->getClassMetadata()->getTableName();
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult($this->getEntityName(), 'q');
        $rsm->addFieldResult('q', 'id', 'id');
        $rsm->addFieldResult('q', 'quote', 'quote');
        $rsm->addFieldResult('q', 'attribution', 'attribution');
        $rsm->addFieldResult('q', 'created_at', 'createdAt');
        $rsm->addFieldResult('q', 'updated_at', 'updatedAt');
        $sql = "
            SELECT
                q.id, q.quote, q.attribution, q.created_at, q.updated_at
            FROM
                {$table} q
            ORDER BY
                {$randomFunc}
            LIMIT 1";
        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        return $query->getOneOrNullResult();
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
