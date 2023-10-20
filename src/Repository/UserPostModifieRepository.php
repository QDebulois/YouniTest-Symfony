<?php

namespace App\Repository;

use App\Entity\UserPostModifie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserPostModifie>
 *
 * @method UserPostModifie|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPostModifie|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserPostModifie[]    findAll()
 * @method UserPostModifie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserPostModifieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserPostModifie::class);
    }

//    /**
//     * @return UserPostModifie[] Returns an array of UserPostModifie objects
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

//    public function findOneBySomeField($value): ?UserPostModifie
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
