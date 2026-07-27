<?php

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Ajoute les en-têtes de sécurité HTTP à toutes les réponses :
 * - X-Frame-Options      : interdit d'afficher le site dans une iframe (anti-clickjacking)
 * - X-Content-Type-Options : empêche le navigateur de "deviner" le type des fichiers (anti-MIME sniffing)
 * - Referrer-Policy      : limite les informations envoyées aux sites externes lors d'un clic sortant
 * - Permissions-Policy   : désactive les API navigateur dont le site n'a pas besoin (caméra, micro, GPS)
 * - Strict-Transport-Security : impose le HTTPS au navigateur pour les visites suivantes (HSTS).
 *   Émis uniquement en production ET sur une réponse déjà servie en HTTPS. La double
 *   condition est volontaire : la spécification interdit le HSTS en clair, et le
 *   restreindre à la prod évite d'« empoisonner » le navigateur en développement
 *   local si le serveur est lancé avec TLS (le HSTS mis en cache force ensuite le
 *   HTTPS sur tout localhost, port compris, pendant un an).
 */
class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire('%kernel.environment%')]
        private readonly string $environment,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $headers = $event->getResponse()->headers;
        $headers->set('X-Frame-Options', 'DENY');
        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        if ('prod' === $this->environment && $event->getRequest()->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }
    }
}
