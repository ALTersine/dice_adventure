<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Ajoute les en-têtes de sécurité HTTP à toutes les réponses :
 * - X-Frame-Options      : interdit d'afficher le site dans une iframe (anti-clickjacking)
 * - X-Content-Type-Options : empêche le navigateur de "deviner" le type des fichiers (anti-MIME sniffing)
 * - Referrer-Policy      : limite les informations envoyées aux sites externes lors d'un clic sortant
 * - Permissions-Policy   : désactive les API navigateur dont le site n'a pas besoin (caméra, micro, GPS)
 */
class SecurityHeadersSubscriber implements EventSubscriberInterface
{
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
    }
}
