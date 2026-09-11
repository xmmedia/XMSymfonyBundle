<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContext;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceGate;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenancePage;

/**
 * Runs MaintenanceGate, for front controllers that don't run it before the kernel's created, &
 * so requests that are allowed through get MaintenanceGate::BYPASS_ATTRIBUTE.
 *
 * Runs after ValidateRequestListener (256), so the client IP has been checked against the
 * trusted proxies, but before the session (128), routing (32) & the firewall (8), so it doesn't
 * need the database: maintenance may be because it's unavailable.
 */
final readonly class MaintenanceSubscriber implements EventSubscriberInterface
{
    // kept for templates & code that used them here
    public const string HEADER = MaintenanceGate::HEADER;
    public const string BYPASS_ATTRIBUTE = MaintenanceGate::BYPASS_ATTRIBUTE;
    public const string ERROR_CODE = MaintenanceGate::ERROR_CODE;
    public const string TEMPLATE = MaintenancePage::TEMPLATE;

    public function __construct(
        private MaintenanceGate $gate,
        private MaintenancePage $page,
        private bool $debug,
        private ?RequestContext $requestContext = null,
    ) {
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

        $request = $event->getRequest();

        // the profiler & debug toolbar (dev only)
        if ($this->debug && str_starts_with($request->getPathInfo(), '/_')) {
            return;
        }

        // routing hasn't run, so URLs would be generated for default_uri, not this request's
        // host (eg the debug toolbar's, injected into the page)
        $this->requestContext?->fromRequest($request);

        $response = $this->gate->handle($request, $this->page->render(...));
        if (null !== $response) {
            $event->setResponse($response);
        }
    }
}
