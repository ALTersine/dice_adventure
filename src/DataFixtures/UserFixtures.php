<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Crée le compte administrateur.
 *
 * Les identifiants sont lus depuis les variables d'environnement
 * ADMIN_USERNAME et ADMIN_PASSWORD (à définir dans .env.local, non versionné),
 * afin qu'aucun mot de passe — même hashé — ne soit commité dans le dépôt.
 */
class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        #[Autowire(env: 'ADMIN_USERNAME')]
        private readonly string $adminUsername,
        #[Autowire(env: 'ADMIN_PASSWORD')]
        private readonly string $adminPassword,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setRoles(['ROLE_ADMIN']);
        $user->setUsername($this->adminUsername);
        $user->setPassword($this->passwordHasher->hashPassword($user, $this->adminPassword));
        $user->setEmail('diceadventure28@gmail.com');
        $manager->persist($user);

        $manager->flush();
    }
}
