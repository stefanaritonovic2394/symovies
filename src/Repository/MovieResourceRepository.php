<?php

namespace App\Repository;

use App\ApiResource\MovieResource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovieResource>
 *
 * @method MovieResource|null find($id, $lockMode = null, $lockVersion = null)
 * @method MovieResource|null findOneBy(array $criteria, array $orderBy = null)
 * @method MovieResource[]    findAll()
 * @method MovieResource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MovieResourceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovieResource::class);
    }

    //    /**
    //     * @return MovieResource[] Returns an array of MovieResource objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?MovieResource
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
