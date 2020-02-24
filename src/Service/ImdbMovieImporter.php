<?php

declare(strict_types=1);

namespace Movifony\Service;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Movifony\DTO\MovieDto;
use Movifony\Entity\ImdbMovie;
use Movifony\Factory\ImbdFactory;

/**
 * Class ImdbMovieImporter
 *
 * @author Corentin Bouix <cbouix@clever-age.com>
 */
class ImdbMovieImporter implements ImporterInterface
{
    protected ManagerRegistry $managerRegistry;

    protected AssetGetterInterface $assetGetter;

    /**
     * @param ManagerRegistry      $managerRegistry
     * @param AssetGetterInterface $assetGetter
     */
    public function __construct(ManagerRegistry $managerRegistry, AssetGetterInterface $assetGetter)
    {
        $this->managerRegistry = $managerRegistry;
        $this->assetGetter = $assetGetter;
    }

    /**
     * @inheritDoc
     */
    public function read(array $data): ?MovieDto
    {
        $identifier = $data['titleId'];
        $isOriginalTitle = (bool) $data['isOriginalTitle'];

        $om = $this->getObjectManager();
        $repo = $om->getRepository(ImdbMovie::class);
        $result = $repo->findOneBy(
            [
                'identifier' => $identifier,
            ]
        );

        if ($result !== null || !$isOriginalTitle) {
            return null;
        }

        return new MovieDto($identifier, $data['title']);
    }

    /**
     * @inheritDoc
     */
    public function process(MovieDto $movieDto): ImdbMovie
    {
        return ImbdFactory::createMovie($movieDto);
    }

    /**
     * @inheritDoc
     */
    public function import(ImdbMovie $movie): bool
    {
        $om = $this->getObjectManager();
        if ($om === null) {
            return false;
        }

        $om->persist($movie);
        $om->flush();

        $this->importAsset($movie);

        return true;
    }

    /**
     * @param ImdbMovie $movie
     */
    protected function importAsset(ImdbMovie $movie): void
    {
        $assetUrl = $this->assetGetter->getPoster($movie->getIdentifier());
        dump($assetUrl);
    }

    public function clear(): void
    {
        $this->managerRegistry->getManager()->clear();
    }

    protected function getObjectManager(): ?ObjectManager
    {
        return $this->managerRegistry->getManagerForClass(ImdbMovie::class);
    }
}
