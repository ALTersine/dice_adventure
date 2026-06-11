<?php

namespace App\DataFixtures;

use App\Entity\Avis;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setRoles(['admin']);
        $user->setUsername('moi');
        $user->setPassword('$2y$13$CrfZKduGthI8mzcprIlXKeOWrcnhKujaGLh.tkZgnHwef3zfx6p8q');
        $manager->persist($user);

        $manager->flush();
    }
}
