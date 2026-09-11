<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceGate;

/**
 * Runs MaintenanceGate again once the kernel has the request, so requests that are allowed
 * through get MaintenanceGate::BYPASS_ATTRIBUTE (the front controller's run is on its own
 * request), & to catch it being turned on in between.
 *
 * Runs after ValidateRequestListener (256), so the client IP has been checked against the
 * trusted proxies, but before the session (128), routing (32) & the firewall (8), so it doesn't
 * need the database: maintenance may be because it's unavailable.
 */
final readonly class MaintenanceSubscriber implements EventSubscriberInterface
{
    public function __construct(private MaintenanceGate $gate)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 255],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $this->gate->handle($event->getRequest());
        if (null !== $response) {
            $event->setResponse($response);
        }
    }
}
