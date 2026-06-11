<?php

namespace App\DataFixtures;

use App\Entity\InfoDuPrix;
use App\Entity\Prix;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PrixFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $prixCampagne = new Prix('Campagne','Sur Devis','pack de 4 séances');
        $manager->persist($prixCampagne);
        $this->addReference('campagne', $prixCampagne);

        $prixSeance = new Prix('Séance','150','séance . 4h');
        $prixSeance->setMettreEnAvant(true);
        $manager->persist($prixSeance);
        $this->addReference('seance', $prixSeance);

        $prixEvt = new Prix('Evenement','Sur Devis','particulier et entreprise');
        $manager->persist($prixEvt);
        $this->addReference('evenement', $prixEvt);

        $manager->flush();
    }
}
