<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Captcha maison sur le thème du site : le visiteur additionne deux dés.
 * La réponse attendue est stockée en session, rien de sensible ne transite
 * côté client et aucun service externe n'est sollicité (RGPD-friendly).
 */
class ContactCaptcha
{
    private const SESSION_KEY = 'contact_captcha_answer';

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * Tire deux dés, mémorise la somme en session et renvoie la question à afficher.
     */
    public function newQuestion(): string
    {
        $de1 = random_int(1, 6);
        $de2 = random_int(1, 6);

        $this->requestStack->getSession()->set(self::SESSION_KEY, $de1 + $de2);

        return sprintf('Anti-robots : combien font %d + %d ?', $de1, $de2);
    }

    /**
     * Vérifie la réponse du visiteur. La réponse attendue est consommée :
     * chaque tentative exige un nouveau captcha (pas de rejeu possible).
     */
    public function verify(?string $reponse): bool
    {
        $session = $this->requestStack->getSession();
        $attendu = $session->get(self::SESSION_KEY);
        $session->remove(self::SESSION_KEY);

        return null !== $attendu
            && null !== $reponse
            && '' !== trim($reponse)
            && (int) trim($reponse) === $attendu;
    }
}
