<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Game|null find($id, $lockMode = null, $lockVersion = null)
 * @method Game|null findOneBy(array $criteria, array $orderBy = null)
 * @method Game[]    findAll()
 * @method Game[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function getGamesPaginated($page, $nbMaxPerPage)
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

        $qb = $this->createQueryBuilder('game')
            ->orderBy('game.createdAt', 'DESC');

        $query = $qb->getQuery();

        $premierResultat = ($page - 1) * $nbMaxPerPage;
        $query->setFirstResult($premierResultat)->setMaxResults($nbMaxPerPage);
        $paginator = new Paginator($query);

        if ( ($paginator->count() <= $premierResultat) && $page != 1) {
            throw new NotFoundHttpException('La page demandée n\'existe pas.'); // page 404, sauf pour la première page
        }

        return $paginator;
    }
}
