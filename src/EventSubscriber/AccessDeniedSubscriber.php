<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;

class AccessDeniedSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Security $security
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // Bajamos la prioridad a 1 para que Symfony Security procese primero
            KernelEvents::EXCEPTION => ['onKernelException', 1],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Si no es un error de acceso denegado, ignoramos
        if (!$exception instanceof AccessDeniedException) {
            return;
        }

        $user = $this->security->getUser();

        // 1. SI NO HAY USUARIO:
        // No hacemos nada. Symfony lo mandará al login automáticamente
        // o le dejará entrar si la ruta es pública (como el registro).
        if (!$user) {
            return;
        }

        // 2. SI EL USUARIO ESTÁ LOGUEADO PERO INTENTA ENTRAR A ADMIN (u otra zona prohibida):
        // En lugar del error 403, lo mandamos a su panel.
        $response = new RedirectResponse($this->urlGenerator->generate('app_user_dashboard'));
        $event->setResponse($response);
    }
}
