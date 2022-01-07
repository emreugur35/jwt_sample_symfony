<?php

namespace App\Repository;

use App\Entity\OrderDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderDetails[]    findAll()
 * @method OrderDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrderDetails::class);
    }

    public function findByOrderId($order_id, $user_id)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.order_id = :val')
            ->setParameter('val', $order_id)
            ->andWhere('o.user_id = :val1')
            ->setParameter('val1', $user_id)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function deleteByOrderId($order_id)
    {

        $conn = $this->getEntityManager()->getConnection();

        $sql = 'DELETE FROM order_details  WHERE order_details.order_id = :order_id ';
        $stmt = $conn->prepare($sql);

        $stmt->executeQuery(['order_id' => $order_id]);
    }



    // /**
    //  * @return OrderDetails[] Returns an array of OrderDetails objects
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
    public function findOneBySomeField($value): ?OrderDetails
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
