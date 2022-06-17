<?php

namespace App\Repository;

use App\Entity\Annonce;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Annonce>
 *
 * @method Annonce|null find($id, $lockMode = null, $lockVersion = null)
 * @method Annonce|null findOneBy(array $criteria, array $orderBy = null)
 * @method Annonce[]    findAll()
 * @method Annonce[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnnonceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Annonce::class);
    }

    public function add(Annonce $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Annonce $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
        * @return Annonce[] Returns an array of Annonce objects
        */
    public function findByTitleField($value): array
    {
        // VERSION QUERY BUILDER
        return $this->createQueryBuilder('a')
            ->andWhere("a.title LIKE :val")
            ->setParameter('val', '%'.$value.'%')
            ->orderBy('a.id', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        // VERSION CREATE QUERY via ENTITY MANAGER
        // $entityManager = $this->getEntityManager();

        // $query = $entityManager->createQuery(
        //     'SELECT a
        //     FROM App\Entity\Annonce a
        //     WHERE a.title LIKE :value
        //     ORDER BY a.id DESC'
        // )->setParameter('value', '%'.$value.'%');

        // // returns an array of Product objects
        // return $query->getResult();
    }

//    public function findOneBySomeField($value): ?Annonce
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
