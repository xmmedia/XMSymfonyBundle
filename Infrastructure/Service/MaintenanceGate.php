<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Service;

use Carbon\CarbonImmutable;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * While maintenance mode is on, answers every request with a 503, except from the allowed IPs &
 * anyone with the key. Page loads get the maintenance page, GraphQL requests & requests accepting
 * JSON get a GraphQL style error with the MAINTENANCE code, so the frontend can tell it from a
 * failure.
 *
 * It only needs the autoloader, so the front controller runs it before the kernel's created
 * (handleGlobals()) & it works even if the app won't boot. MaintenanceSubscriber runs it too,
 * for front controllers that don't.
 */
final readonly class MaintenanceGate
{
    // sent with every maintenance response, eg so a deploy can tell maintenance from a failure
    public const string HEADER = 'X-Maintenance';
    // set on requests from an allowed IP or with the key, eg to show them it's on
    public const string BYPASS_ATTRIBUTE = '_maintenance_bypass';
    public const string ERROR_CODE = 'MAINTENANCE';
    // the key, generated each time it's turned on, lets anyone with it use the site: added to any
    // URL, it's moved to the cookie. Or sent in the header, eg by an API client
    public const string KEY_PARAMETER = 'maintenance-key';
    public const string KEY_COOKIE = 'maintenance-key';
    public const string KEY_HEADER = 'X-Maintenance-Key';

    private const string GRAPHQL_PATH = '/graphql';
    // when the page hasn't been rendered (the file was created by hand) & there's no app to render it
    private const string FALLBACK_PAGE = <<<'HTML'
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta http-equiv="refresh" content="60">
            <title>Maintenance</title>
        </head>
        <body style="font-family: ui-sans-serif, system-ui, sans-serif; text-align: center; padding: 3rem 1rem;">
            <h1>We're doing some maintenance</h1>
            <p>We'll be back shortly.</p>
        </body>
        </html>
        HTML;

    public function __construct(private MaintenanceMode $maintenanceMode)
    {
    }

    /**
     * For the front controller, before the kernel's created: the maintenance response for the
     * current request, which the Runtime sends when it's returned instead of the kernel. Null if
     * it can carry on.
     */
    public static function handleGlobals(string $file): ?Response
    {
        return (new self(new MaintenanceMode($file)))->handle(Request::createFromGlobals());
    }

    /**
     * @param (callable(MaintenanceSettings): string)|null $renderPage for when the page hasn't been
     *                                                                 rendered to a file
     *
     * @return Response|null null if the request can carry on: it's off, or they're allowed
     */
    public function handle(Request $request, ?callable $renderPage = null): ?Response
    {
        $settings = $this->maintenanceMode->settings();
        if (null === $settings) {
            return null;
        }

        if ($settings->allows($request->getClientIp())
            || $settings->allowsKey($request->headers->get(self::KEY_HEADER))
            || $settings->allowsKey($request->cookies->get(self::KEY_COOKIE))) {
            $request->attributes->set(self::BYPASS_ATTRIBUTE, true);

            return null;
        }

        $key = $request->query->all()[self::KEY_PARAMETER] ?? null;
        if (\is_string($key) && $settings->allowsKey($key)) {
            return $this->keyResponse($request, $key);
        }

        if ($this->wantsJson($request)) {
            $response = $this->jsonResponse($settings);
        } else {
            $page = $this->maintenanceMode->page();
            if (null === $page && null !== $renderPage) {
                $page = $renderPage($settings);
            }

            $response = new Response($page ?? self::FALLBACK_PAGE, Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $response->headers->set(self::HEADER, '1');
        $response->headers->set('Retry-After', (string) $settings->retryAfter(CarbonImmutable::now()));
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    /**
     * Sets the cookie & redirects to the URL without the key, so it's not left in the address
     * bar, history or a bookmark.
     */
    private function keyResponse(Request $request, string $key): RedirectResponse
    {
        $query = $request->query->all();
        unset($query[self::KEY_PARAMETER]);

        $url = $request->getSchemeAndHttpHost().$request->getBaseUrl().$request->getPathInfo();
        if ([] !== $query) {
            $url .= '?'.http_build_query($query);
        }

        $response = new RedirectResponse($url);
        // until the browser's closed; a new key is generated next time it's turned on
        $response->headers->setCookie(Cookie::create(self::KEY_COOKIE, $key, 0, '/', null, $request->isSecure()));
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
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
}
