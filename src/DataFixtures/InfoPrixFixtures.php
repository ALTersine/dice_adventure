<?php

namespace App\DataFixtures;

use App\Entity\FraisKM;
use App\Entity\InfoDuPrix;
use App\Entity\Prix;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InfoPrixFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {

        $prixCampagne = $this->getReference('campagne', Prix::class);
        $manager->persist(new InfoDuPrix('Une histoire au long cours', $prixCampagne));
        $manager->persist(new InfoDuPrix('Personnages qui évoluent', $prixCampagne));
        $manager->persist(new InfoDuPrix('Séances régulières à votre rythme', $prixCampagne));

        $prixSeance = $this->getReference('seance', Prix::class);
        $manager->persist(new InfoDuPrix('Découverte du jeu de rôle pour 3 à 6 joueurs', $prixSeance));
        $manager->persist(new InfoDuPrix('Personnages prêts à jouer', $prixSeance));
        $manager->persist(new InfoDuPrix('Un scénario d\'une séance', $prixSeance));

        $prixEvt = $this->getReference('evenement', Prix::class);
        $manager->persist(new InfoDuPrix('Anniversaires, team building, EVJF/EVG (Enterrement Vie de Jeune Fille / Garçon), cohésion d`\'équipe...', $prixEvt));
        $manager->persist(new InfoDuPrix('Formule entièrement adaptable à vos besoins', $prixEvt));
        $manager->persist(new InfoDuPrix('Scénario et thématique sur mesure', $prixEvt));

        $fraisKM = new FraisKM();
        $fraisKM->setInfo(null);
        $manager->persist($fraisKM);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            PrixFixtures::class,
        ];
    }

}
