<?php

namespace App\Repository;

use App\Entity\Friend;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Friend|null find($id, $lockMode = null, $lockVersion = null)
 * @method Friend|null findOneBy(array $criteria, array $orderBy = null)
 * @method Friend[]    findAll()
 * @method Friend[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FriendRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Friend::class);
    }



    public function getFriends($user)
    {
        $friends = $this->createQueryBuilder('friend')
            ->where('friend.sender = :user OR friend.recipient = :user')
            ->andWhere('friend.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 1)
            ->getQuery()
            ->getResult();

        return $friends;
    }

    public function getFriendRequestsReceived($id)
    {
        $friendRequests = $this->createQueryBuilder('friend')
            ->where('friend.recipient = :id')
            ->andWhere('friend.status = :status')
            ->setParameter('id', $id)
            ->setParameter('status', 0)
            ->groupBy('friend.sender')
            ->getQuery()
            ->getResult();

        return $friendRequests;
    }

    public function getFriendRequestsSent($id)
    {
        $myRequests = $this->createQueryBuilder('friend')
            ->where('friend.sender = :id')
            ->andWhere('friend.status = :status')
            ->setParameter('id', $id)
            ->setParameter('status', 0)
            ->groupBy('friend.recipient')
            ->getQuery()
            ->getResult();

        return $myRequests;
    }

}
