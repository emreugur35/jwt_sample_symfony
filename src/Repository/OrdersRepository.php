<?php

namespace App\Repository;

use App\Entity\Orders;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Orders|null find($id, $lockMode = null, $lockVersion = null)
 * @method Orders|null findOneBy(array $criteria, array $orderBy = null)
 * @method Orders[]    findAll()
 * @method Orders[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrdersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Orders::class);
    }


    public function findByUser($user_id)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.user_id = :val')
            ->setParameter('val', $user_id)
            ->orderBy('o.id', 'ASC')
          
            ->getQuery()
            ->getResult()
        ;
    }


    public function findOrdersByDateandUser($date,$user_id)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.shipping_date <= :val')
            ->setParameter('val', $date)
            ->andWhere('o.user_id = :val')
            ->setParameter('val', $user_id)
            ->orderBy('o.id', 'ASC')
          
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByOrderId($order_id, $user_id)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.id = :val')
            ->setParameter('val', $order_id)
            ->andWhere('o.user_id = :val1')
            ->setParameter('val1', $user_id)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getSingleResult();
    }


  

    // /**
    //  * @return Orders[] Returns an array of Orders objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Orders
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
