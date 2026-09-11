<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSubscriber;

use Carbon\CarbonImmutable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContext;
use Twig\Environment;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceMode;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceSettings;

/**
 * While maintenance mode is on, answers every request with a 503, except from the allowed IPs.
 * Page loads get the maintenance page (@XmSymfony/maintenance.html.twig). GraphQL requests &
 * requests accepting JSON get a GraphQL style error with the MAINTENANCE code, so the frontend
 * can tell it from a failure.
 *
 * Runs after ValidateRequestListener (256), so the client IP has been checked against the
 * trusted proxies, but before the session (128), routing (32) & the firewall (8), so it doesn't
 * need the database: maintenance may be because it's unavailable.
 */
final readonly class MaintenanceSubscriber implements EventSubscriberInterface
{
    // sent with every maintenance response, eg so a deploy can tell maintenance from a failure
    public const string HEADER = 'X-Maintenance';
    // set on requests from an allowed IP, eg to show them it's on
    public const string BYPASS_ATTRIBUTE = '_maintenance_bypass';
    public const string ERROR_CODE = 'MAINTENANCE';
    public const string TEMPLATE = '@XmSymfony/maintenance.html.twig';

    private const string GRAPHQL_PATH = '/graphql';

    public function __construct(
        private MaintenanceMode $maintenanceMode,
        private Environment $twig,
        private bool $debug,
        private ?string $timeZone = null,
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

        $settings = $this->maintenanceMode->settings();
        if (null === $settings) {
            return;
        }

        $request = $event->getRequest();

        if ($settings->allows($request->getClientIp())) {
            $request->attributes->set(self::BYPASS_ATTRIBUTE, true);

            return;
        }

        // the profiler & debug toolbar (dev only)
        if ($this->debug && str_starts_with($request->getPathInfo(), '/_')) {
            return;
        }

        // routing hasn't run, so URLs would be generated for default_uri, not this request's
        // host (eg the debug toolbar's, injected into the page)
        $this->requestContext?->fromRequest($request);

        if ($this->wantsJson($request)) {
            $response = $this->jsonResponse($settings);
        } else {
            $response = $this->htmlResponse($settings);
        }

        $response->headers->set(self::HEADER, '1');
        $response->headers->set('Retry-After', (string) $settings->retryAfter(CarbonImmutable::now()));
        $response->headers->set('Cache-Control', 'no-store');

        $event->setResponse($response);
    }

    private function wantsJson(Request $request): bool
    {
        if (str_starts_with($request->getPathInfo(), self::GRAPHQL_PATH)) {
            return true;
        }

        return 'json' === $request->getPreferredFormat();
    }

    private function jsonResponse(MaintenanceSettings $settings): JsonResponse
    {
        return new JsonResponse(
            [
                'errors' => [
                    [
                        'message'    => $settings->displayMessage(),
                        'extensions' => [
                            'code'  => self::ERROR_CODE,
                            'until' => $settings->until()?->format(\DATE_ATOM),
                        ],
                    ],
                ],
            ],
            Response::HTTP_SERVICE_UNAVAILABLE,
        );
    }

    private function htmlResponse(MaintenanceSettings $settings): Response
    {
        $until = null;
        if (null !== $settings->until()) {
            $until = CarbonImmutable::instance($settings->until())
                ->setTimezone($this->timeZone ?? date_default_timezone_get());
        }

        return new Response(
            $this->twig->render(self::TEMPLATE, [
                'message' => $settings->displayMessage(),
                'until'   => $until,
            ]),
            Response::HTTP_SERVICE_UNAVAILABLE,
        );
    }
}
