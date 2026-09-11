<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\EventSubscriber;

use Carbon\CarbonImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Twig\Loader\FilesystemLoader;
use Xm\SymfonyBundle\EventSubscriber\MaintenanceSubscriber;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceMode;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceSettings;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class MaintenanceSubscriberTest extends BaseTestCase
{
    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function testSubscribedEvents(): void
    {
        $this->assertSame(
            ['onKernelRequest', 255],
            MaintenanceSubscriber::getSubscribedEvents()[KernelEvents::REQUEST],
        );
    }

    public function testOff(): void
    {
        $event = $this->requestEvent(Request::create('/'));

        $this->subscriber(null)->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    public function testPage(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-10 12:00', 'America/Edmonton'));
        $message = $this->faker()->sentence();
        $settings = new MaintenanceSettings($message, CarbonImmutable::now()->addMinutes(30));
        $event = $this->requestEvent(Request::create('/'));

        $this->subscriber($settings, $this->bundleTwig())->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertSame('1', $response->headers->get(MaintenanceSubscriber::HEADER));
        $this->assertSame('1800', $response->headers->get('Retry-After'));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('We\'re doing some maintenance', $response->getContent());
        $this->assertStringContainsString(htmlspecialchars($message), $response->getContent());
        $this->assertStringContainsString('12:30 pm MDT', $response->getContent());
    }

    public function testPageRenderedWithSettings(): void
    {
        $message = $this->faker()->sentence();
        $until = CarbonImmutable::now()->addHour();

        $twig = \Mockery::mock(Environment::class);
        $twig->shouldReceive('render')
            ->once()
            ->withArgs(static function (string $template, array $context) use ($message, $until): bool {
                return MaintenanceSubscriber::TEMPLATE === $template
                    && $message === $context['message']
                    && $until->getTimestamp() === $context['until']->getTimestamp()
                    && 'America/Halifax' === $context['until']->getTimezone()->getName();
            })
            ->andReturn('page');

        $event = $this->requestEvent(Request::create('/'));

        (new MaintenanceSubscriber(
            $this->maintenanceMode(new MaintenanceSettings($message, $until)),
            $twig,
            false,
            'America/Halifax',
        ))->onKernelRequest($event);

        $this->assertSame('page', $event->getResponse()->getContent());
    }

    public function testGraphQl(): void
    {
        $message = $this->faker()->sentence();
        $until = CarbonImmutable::now()->addHour()->startOfSecond();
        $event = $this->requestEvent(Request::create('/graphql/batch', 'POST'));

        $this->subscriber(new MaintenanceSettings($message, $until))->onKernelRequest($event);

        $response = $event->getResponse();
        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $response->getStatusCode());
        $this->assertSame('1', $response->headers->get(MaintenanceSubscriber::HEADER));
        $this->assertSame(
            [
                'errors' => [
                    [
                        'message'    => $message,
                        'extensions' => [
                            'code'  => MaintenanceSubscriber::ERROR_CODE,
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
        $event = $this->requestEvent($request);

        $this->subscriber(new MaintenanceSettings())->onKernelRequest($event);

        $content = json_decode($event->getResponse()->getContent(), true);
        $this->assertSame(MaintenanceSubscriber::ERROR_CODE, $content['errors'][0]['extensions']['code']);
        $this->assertSame(MaintenanceSettings::DEFAULT_MESSAGE, $content['errors'][0]['message']);
        $this->assertNull($content['errors'][0]['extensions']['until']);
    }

    public function testAllowedIp(): void
    {
        $ip = $this->faker()->ipv4();
        $request = Request::create('/', server: ['REMOTE_ADDR' => $ip]);
        $event = $this->requestEvent($request);

        $this->subscriber(new MaintenanceSettings(null, null, [$ip]))->onKernelRequest($event);

        $this->assertNull($event->getResponse());
        $this->assertTrue($request->attributes->get(MaintenanceSubscriber::BYPASS_ATTRIBUTE));
    }

    public function testOtherIp(): void
    {
        $request = Request::create('/graphql', server: ['REMOTE_ADDR' => '10.0.0.2']);
        $event = $this->requestEvent($request);

        $this->subscriber(new MaintenanceSettings(null, null, ['10.0.0.1']))->onKernelRequest($event);

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $event->getResponse()->getStatusCode());
        $this->assertFalse($request->attributes->has(MaintenanceSubscriber::BYPASS_ATTRIBUTE));
    }

    public function testProfilerInDebug(): void
    {
        $event = $this->requestEvent(Request::create('/_wdt/abc123'));

        $this->subscriber(new MaintenanceSettings(), null, true)->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    public function testProfilerPathNotSkippedWithoutDebug(): void
    {
        $event = $this->requestEvent(Request::create('/_wdt/abc123'));

        $this->subscriber(new MaintenanceSettings())->onKernelRequest($event);

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $event->getResponse()->getStatusCode());
    }

    public function testSubRequest(): void
    {
        $event = new RequestEvent(
            \Mockery::mock(HttpKernelInterface::class),
            Request::create('/'),
            HttpKernelInterface::SUB_REQUEST,
        );

        $this->subscriber(new MaintenanceSettings())->onKernelRequest($event);

        $this->assertNull($event->getResponse());
    }

    private function subscriber(
        ?MaintenanceSettings $settings,
        ?Environment $twig = null,
        bool $debug = false,
    ): MaintenanceSubscriber {
        return new MaintenanceSubscriber(
            $this->maintenanceMode($settings),
            $twig ?? new Environment(new ArrayLoader([MaintenanceSubscriber::TEMPLATE => 'page'])),
            $debug,
            'America/Edmonton',
        );
    }

    private function maintenanceMode(?MaintenanceSettings $settings): MaintenanceMode
    {
        $maintenanceMode = \Mockery::mock(MaintenanceMode::class);
        $maintenanceMode->shouldReceive('settings')->andReturn($settings);

        return $maintenanceMode;
    }

    private function bundleTwig(): Environment
    {
        $loader = new FilesystemLoader();
        $loader->addPath(__DIR__.'/../../Resources/views', 'XmSymfony');

        return new Environment($loader, ['strict_variables' => true]);
    }

    private function requestEvent(Request $request): RequestEvent
    {
        return new RequestEvent(
            \Mockery::mock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );
    }
}
