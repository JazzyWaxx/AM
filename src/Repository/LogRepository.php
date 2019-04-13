<?php

namespace App\Repository;

use App\Entity\Log;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Log|null find($id, $lockMode = null, $lockVersion = null)
 * @method Log|null findOneBy(array $criteria, array $orderBy = null)
 * @method Log[]    findAll()
 * @method Log[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Log::class);
    }

    public function lastConnected($id) {
        $lastConnection = $this->createQueryBuilder('log')
            ->where('log.user = :id')
            ->orderBy('log.loggedAt', 'DESC')
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        return $lastConnection;
    }
}
