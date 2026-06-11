<?php

namespace App\DataFixtures;

use App\Entity\Avis;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AvisFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $avis1 = new Avis();
        $avis1->setAuteur('Alexia');
        $avis1->setInfosSuppAuteur('joueuse régulière');
        $avis1->setMessage('Les aventures sont vraiment incroyables, toutes mes sessions étaient de grandes réussite. Toujours hâte d\'en refaire');
        $avis1->setNote(5);
        $manager->persist($avis1);

        $manager->flush();
    }
}
