<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Service\ContactCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Traite le formulaire « Contacter le maître du jeu » de la page d'accueil :
 * anti-spam (honeypot + captcha), validation des champs, puis envoi du mail
 * à l'adresse du compte administrateur.
 */
class ContactController extends AbstractController
{
    private const NOM_MAX     = 100;
    private const EMAIL_MAX   = 180;
    private const MESSAGE_MAX = 5000;

    public function __construct(
        #[Autowire(env: 'ADMIN_USERNAME')]
        private readonly string $adminUsername,
    ) {
    }

    #[Route('/contact', name: 'app_contact', methods: ['POST'])]
    public function send(
        Request $request,
        MailerInterface $mailer,
        UserRepository $userRepository,
        ContactCaptcha $captcha,
    ): Response {
        // Honeypot : ce champ est invisible pour un humain, seuls les robots
        // le remplissent. On simule alors un succès pour ne pas les renseigner.
        if ('' !== (string) $request->request->get('site_web')) {
            $this->addFlash('contact_success', 'Votre demande a bien été envoyée.');

            return $this->redirectToContact();
        }

        if (!$this->isCsrfTokenValid('contact_form', (string) $request->request->get('_token'))) {
            $this->addFlash('contact_error', 'Jeton de sécurité invalide, veuillez réessayer.');

            return $this->redirectToContact();
        }

        if (!$captcha->verify($request->request->get('captcha'))) {
            $this->addFlash('contact_error', 'Le total des dés est incorrect, retentez votre jet !');

            return $this->redirectToContact();
        }

        $nom     = trim((string) $request->request->get('nom'));
        $email   = trim((string) $request->request->get('email'));
        $message = trim((string) $request->request->get('message'));

        if ('' === $nom || mb_strlen($nom) > self::NOM_MAX
            || '' === $message || mb_strlen($message) > self::MESSAGE_MAX
            || mb_strlen($email) > self::EMAIL_MAX || false === filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            $this->addFlash('contact_error', 'Merci de vérifier les champs saisis (nom, email valide et message).');

            return $this->redirectToContact();
        }

        $destinataire = $userRepository->findOneBy(['username' => $this->adminUsername])?->getEmail();
        if (null === $destinataire) {
            $this->addFlash('contact_error', "L'envoi est momentanément indisponible, veuillez réessayer plus tard.");

            return $this->redirectToContact();
        }

        $mail = (new Email())
            ->from(new Address($destinataire, 'Dice Adventure'))
            ->to($destinataire)
            ->replyTo(new Address($email, $nom))
            ->subject('Nouvelle demande d\'aventure de '.$nom)
            ->text(<<<TXT
                Nouvelle demande envoyée depuis le formulaire « Contacter le maître du jeu » :

                Nom : {$nom}
                Email : {$email}

                Message :
                {$message}
                TXT);

        $mailer->send($mail);

        $this->addFlash('contact_success', 'Votre quête est lancée ! Le maître du jeu vous répond sous peu.');

        return $this->redirectToContact();
    }

    /**
     * Redirige vers la section contact de l'accueil (303 : compatible Turbo).
     */
    private function redirectToContact(): Response
    {
        return $this->redirect($this->generateUrl('app_home').'#contact', Response::HTTP_SEE_OTHER);
    }
}
