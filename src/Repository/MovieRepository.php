<?php

declare(strict_types=1);

namespace Movifony\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Movifony\Entity\ImdbMovie;

/**
 * Retrieve movie from DB
 *
 * @author Corentin Bouix <cbouix@clever-age.com>
 */
class MovieRepository extends ServiceEntityRepository
{
    /**
     * @param string $entityClass The class name of the entity this repository manages
     */
    public function __construct(ManagerRegistry $registry, string $entityClass = ImdbMovie::class)
    {
        parent::__construct($registry, $entityClass);
    }

    /**
     * Find latest movies with available URL asset posters
     *
     * @param int $limit
     *
     * @return array|ImdbMovie[]
     */
    public function findLatestMovies(int $limit): array
    {
        $qb = $this->createQueryBuilder('m');
        $qb->where($qb->expr()->isNotNull('m.posterUrl'))
            ->setMaxResults($limit)
            ->orderBy('m.id', 'DESC');

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param string $identifier
     *
     * @return ImdbMovie|null
     */
    public function findByIdentifier(string $identifier): ?ImdbMovie
    {
        /** @var $movie ImdbMovie */
        $movie = $this->findOneBy(['identifier' => $identifier]);

        return $movie;
    }
}
