<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * Actualités publiées (visibles sur le site), de la plus récente à la plus ancienne.
     *
     * @return Evenement[]
     */
    public function findPubliees(): array
    {
        return $this->findBy(['publier' => true], ['id' => 'DESC']);
    }

    /**
     * Actualités archivées (retirées du site mais conservées en base).
     *
     * @return Evenement[]
     */
    public function findArchivees(): array
    {
        return $this->findBy(['publier' => false], ['id' => 'DESC']);
    }

    /**
     * Crée une actualité (publiée par défaut) et l'enregistre en base.
     *
     * @param string|null $image Nom du fichier image déjà déposé dans public/uploads/actualites/
     */
    public function create(string $titre, string $description, ?string $image = null): Evenement
    {
        $evenement = new Evenement();
        $evenement->setTitre($titre);
        $evenement->setDescription($description);
        $evenement->setPublier(true);
        $evenement->setImage($image);

        $em = $this->getEntityManager();
        $em->persist($evenement);
        $em->flush();

        return $evenement;
    }

    /**
     * Archive une actualité : la retire du site tout en gardant la trace en base.
     */
    public function archive(Evenement $evenement): void
    {
        $evenement->setPublier(false);
        $this->getEntityManager()->flush();
    }

    /**
     * Republie une actualité précédemment archivée.
     */
    public function publier(Evenement $evenement): void
    {
        $evenement->setPublier(true);
        $this->getEntityManager()->flush();
    }
}
