<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Crée (ou met à jour) le compte administrateur, sans passer par les fixtures.
 *
 * Les fixtures ne doivent JAMAIS être exécutées en production :
 * doctrine:fixtures:load purge la base avant de charger, ce qui effacerait
 * les tarifs, avis et frais kilométriques saisis par le client.
 *
 * Usage :
 *   php bin/console app:admin:create <identifiant> <email>
 *
 * Le mot de passe est demandé en saisie masquée : il n'apparaît ni dans
 * l'historique du shell ni dans la liste des processus.
 */
#[AsCommand(
    name: 'app:admin:create',
    description: 'Crée ou met à jour le compte administrateur (à utiliser en production à la place des fixtures).',
)]
class CreateAdminCommand extends Command
{
    private const PASSWORD_MIN_LENGTH = 12;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('username', InputArgument::REQUIRED, "Identifiant de connexion de l'administrateur")
            ->addArgument('email', InputArgument::REQUIRED, 'Adresse e-mail (destinataire du formulaire de contact)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io       = new SymfonyStyle($input, $output);
        $username = trim((string) $input->getArgument('username'));
        $email    = trim((string) $input->getArgument('email'));

        if ('' === $username) {
            $io->error('L\'identifiant ne peut pas être vide.');

            return Command::FAILURE;
        }

        if (false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error(sprintf('« %s » n\'est pas une adresse e-mail valide.', $email));

            return Command::FAILURE;
        }

        $password = $io->askHidden('Mot de passe du compte (saisie masquée)');
        if (null === $password || mb_strlen($password) < self::PASSWORD_MIN_LENGTH) {
            $io->error(sprintf('Mot de passe requis : %d caractères minimum.', self::PASSWORD_MIN_LENGTH));

            return Command::FAILURE;
        }

        $confirmation = $io->askHidden('Confirmez le mot de passe');
        if ($password !== $confirmation) {
            $io->error('Les deux saisies ne correspondent pas.');

            return Command::FAILURE;
        }

        $user      = $this->userRepository->findOneBy(['username' => $username]);
        $isUpdate  = null !== $user;
        $user    ??= new User();

        $user->setUsername($username);
        $user->setEmail($email);
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf(
            $isUpdate
                ? 'Compte administrateur « %s » mis à jour (mot de passe et e-mail remplacés).'
                : 'Compte administrateur « %s » créé.',
            $username,
        ));

        return Command::SUCCESS;
    }
}
