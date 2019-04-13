<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use InvalidArgumentException;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function getUsersPaginated($page, $nbMaxPerPage)
    {
        if (!is_numeric($page)) {
            throw new InvalidArgumentException(
                'La valeur de l\'argument $page est incorrecte (valeur : ' . $page . ').'
            );
        }

        if ($page < 1) {
            throw new NotFoundHttpException('La page demandée n\'existe pas');
        }

        if (!is_numeric($nbMaxPerPage)) {
            throw new InvalidArgumentException(
                'La valeur de l\'argument $nbMaxParPage est incorrecte (valeur : ' . $nbMaxPerPage . ').'
            );
        }

        $qb = $this->createQueryBuilder('user')
            ->orderBy('user.createdAt', 'DESC');

        $query = $qb->getQuery();

        $premierResultat = ($page - 1) * $nbMaxPerPage;
        $query->setFirstResult($premierResultat)->setMaxResults($nbMaxPerPage);
        $paginator = new Paginator($query);

        if ( ($paginator->count() <= $premierResultat) && $page != 1) {
            throw new NotFoundHttpException('La page demandée n\'existe pas.'); // page 404, sauf pour la première page
        }

        return $paginator;
    }

    public function suggestions() {
        $suggestions = $this->createQueryBuilder('user')
            ->orderBy('user.createdAt', 'DESC')
            ->setMaxResults(200)
            ->getQuery()
            ->getResult();

        return $suggestions;
    }

}
