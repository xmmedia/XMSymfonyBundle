<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\Service;

use Carbon\CarbonImmutable;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceGate;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceMode;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceSettings;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class MaintenanceGateTest extends BaseTestCase
{
    public function testOff(): void
    {
        $this->assertNull($this->gate(null)->handle(Request::create('/')));
    }

    public function testPage(): void
    {
        $page = $this->faker()->randomHtml();
        $until = CarbonImmutable::now()->addMinutes(30)->startOfSecond();

        $response = $this->gate(new MaintenanceSettings(null, $until), $page)->handle(Request::create('/'));

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertSame($page, $response->getContent());
        $this->assertSame('1', $response->headers->get(MaintenanceGate::HEADER));
        $this->assertEqualsWithDelta(1800, (int) $response->headers->get('Retry-After'), 1);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function testPageRenderedWhenThereIsNoFile(): void
    {
        $page = $this->faker()->randomHtml();
        $settings = new MaintenanceSettings($this->faker()->sentence());

        $response = $this->gate($settings)->handle(
            Request::create('/'),
            function (MaintenanceSettings $given) use ($settings, $page): string {
                $this->assertSame($settings, $given);

                return $page;
            },
        );

        $this->assertSame($page, $response->getContent());
    }

    public function testFallbackPage(): void
    {
        $response = $this->gate(new MaintenanceSettings())->handle(Request::create('/'));

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertStringContainsString('We\'re doing some maintenance', $response->getContent());
    }

    public function testGraphQl(): void
    {
        $message = $this->faker()->sentence();
        $until = CarbonImmutable::now()->addHour()->startOfSecond();

        $response = $this->gate(new MaintenanceSettings($message, $until))
            ->handle(Request::create('/graphql/batch', 'POST'));

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertSame('1', $response->headers->get(MaintenanceGate::HEADER));
        $this->assertSame(
            [
                'errors' => [
                    [
                        'message'    => $message,
                        'extensions' => [
                            'code'  => MaintenanceGate::ERROR_CODE,
                            'until' => $until->format(\DATE_ATOM),
                        ],
                    ],
                ],
            ],
            json_decode($response->getContent(), true),
        );
    }

    public function testJsonWhenAccepted(): void
    {
        $request = Request::create('/session-info');
        $request->headers->set('Accept', 'application/json');

        $response = $this->gate(new MaintenanceSettings())->handle($request);

        $content = json_decode($response->getContent(), true);
        $this->assertSame(MaintenanceGate::ERROR_CODE, $content['errors'][0]['extensions']['code']);
        $this->assertSame(MaintenanceSettings::DEFAULT_MESSAGE, $content['errors'][0]['message']);
        $this->assertNull($content['errors'][0]['extensions']['until']);
    }

    public function testAllowedIp(): void
    {
        $ip = $this->faker()->ipv4();
        $request = Request::create('/', server: ['REMOTE_ADDR' => $ip]);

        $this->assertNull($this->gate(new MaintenanceSettings(null, null, [$ip]))->handle($request));
        $this->assertTrue($request->attributes->get(MaintenanceGate::BYPASS_ATTRIBUTE));
    }

    public function testOtherIp(): void
    {
        $request = Request::create('/graphql', server: ['REMOTE_ADDR' => '10.0.0.2']);

        $response = $this->gate(new MaintenanceSettings(null, null, ['10.0.0.1']))->handle($request);

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertFalse($request->attributes->has(MaintenanceGate::BYPASS_ATTRIBUTE));
    }

    public function testKeyInCookie(): void
    {
        $key = MaintenanceSettings::generateKey();
        $request = Request::create('/', cookies: [MaintenanceGate::KEY_COOKIE => $key]);

        $this->assertNull($this->gate(new MaintenanceSettings(null, null, [], $key))->handle($request));
        $this->assertTrue($request->attributes->get(MaintenanceGate::BYPASS_ATTRIBUTE));
    }

    public function testKeyInHeader(): void
    {
        $key = MaintenanceSettings::generateKey();
        $request = Request::create('/graphql', 'POST', server: ['HTTP_X_MAINTENANCE_KEY' => $key]);

        $this->assertNull($this->gate(new MaintenanceSettings(null, null, [], $key))->handle($request));
        $this->assertTrue($request->attributes->get(MaintenanceGate::BYPASS_ATTRIBUTE));
    }

    public function testWrongKey(): void
    {
        $settings = new MaintenanceSettings(null, null, [], MaintenanceSettings::generateKey());
        $request = Request::create(
            '/?'.MaintenanceGate::KEY_PARAMETER.'='.MaintenanceSettings::generateKey(),
            cookies: [MaintenanceGate::KEY_COOKIE => MaintenanceSettings::generateKey()],
            server: ['HTTP_X_MAINTENANCE_KEY' => MaintenanceSettings::generateKey()],
        );

        $response = $this->gate($settings)->handle($request);

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertFalse($request->attributes->has(MaintenanceGate::BYPASS_ATTRIBUTE));
    }

    public function testKeyInQuerySetsTheCookie(): void
    {
        $key = MaintenanceSettings::generateKey();
        $request = Request::create('https://example.com/admin/users?page=2&'.MaintenanceGate::KEY_PARAMETER.'='.$key);

        $response = $this->gate(new MaintenanceSettings(null, null, [], $key))->handle($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('https://example.com/admin/users?page=2', $response->getTargetUrl());

        $cookie = $response->headers->getCookies()[0];
        $this->assertSame(MaintenanceGate::KEY_COOKIE, $cookie->getName());
        $this->assertSame($key, $cookie->getValue());
        $this->assertSame(0, $cookie->getExpiresTime());
        $this->assertTrue($cookie->isSecure());
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame(Cookie::SAMESITE_LAX, $cookie->getSameSite());
    }

    public function testKeyInQueryWithoutOtherParameters(): void
    {
        $key = MaintenanceSettings::generateKey();
        $request = Request::create('http://example.com/?'.MaintenanceGate::KEY_PARAMETER.'='.$key);

        $response = $this->gate(new MaintenanceSettings(null, null, [], $key))->handle($request);

        $this->assertSame('http://example.com/', $response->getTargetUrl());
        $this->assertFalse($response->headers->getCookies()[0]->isSecure());
    }

    public function testNoKeyAllowsNoOne(): void
    {
        $request = Request::create('/', cookies: [MaintenanceGate::KEY_COOKIE => '']);

        $response = $this->gate(new MaintenanceSettings())->handle($request);

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
    }

    private function gate(?MaintenanceSettings $settings, ?string $page = null): MaintenanceGate
    {
        $maintenanceMode = \Mockery::mock(MaintenanceMode::class);
        $maintenanceMode->shouldReceive('settings')->andReturn($settings);
        $maintenanceMode->shouldReceive('page')->andReturn($page);

        return new MaintenanceGate($maintenanceMode);
    }
}
