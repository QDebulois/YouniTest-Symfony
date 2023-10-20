<?php

namespace App\Repository;

use App\Entity\UserCategoryModifie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserCategoryModifie>
 *
 * @method UserCategoryModfifie|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserCategoryModfifie|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserCategoryModfifie[]    findAll()
 * @method UserCategoryModfifie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserCategoryModifieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCategoryModifie::class);
    }

//    /**
//     * @return UserCategoryModfifie[] Returns an array of UserCategoryModfifie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?UserCategoryModfifie
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
