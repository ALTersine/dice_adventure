<?php

namespace App\Repository;

use App\Entity\FraisKM;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FraisKM>
 */
class FraisKMRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FraisKM::class);
    }

    /**
     * Retourne le texte des frais kilométriques stocké en base,
     * ou null si aucun enregistrement n'existe encore.
     */
    public function getInfo(): ?string
    {
        $fraisKM = $this->findOneBy([], ['id' => 'ASC']);

        return $fraisKM?->getInfo();
    }

    /**
     * Remplace le texte des frais kilométriques et enregistre en base.
     * Crée l'enregistrement s'il n'existe pas encore.
     */
    public function replaceInfo(string $info): FraisKM
    {
        $fraisKM = $this->findOneBy([], ['id' => 'ASC']) ?? new FraisKM();
        $fraisKM->setInfo($info);

        $em = $this->getEntityManager();
        $em->persist($fraisKM);
        $em->flush();

        return $fraisKM;
    }
}
