<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\EventSubscriber;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContext;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use Xm\SymfonyBundle\EventSubscriber\MaintenanceSubscriber;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceGate;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceMode;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenancePage;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceSettings;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class MaintenanceSubscriberTest extends BaseTestCase
{
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

    public function testOnRendersThePageWhenThereIsNoFile(): void
    {
        $event = $this->requestEvent(Request::create('/'));

        $this->subscriber(new MaintenanceSettings())->onKernelRequest($event);

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $event->getResponse()->getStatusCode());
        $this->assertSame('1', $event->getResponse()->headers->get(MaintenanceGate::HEADER));
        $this->assertSame('rendered page', $event->getResponse()->getContent());
    }

    public function testAllowed(): void
    {
        $ip = $this->faker()->ipv4();
        $request = Request::create('/', server: ['REMOTE_ADDR' => $ip]);
        $event = $this->requestEvent($request);

        $this->subscriber(new MaintenanceSettings(null, null, [$ip]))->onKernelRequest($event);

        $this->assertNull($event->getResponse());
        $this->assertTrue($request->attributes->get(MaintenanceSubscriber::BYPASS_ATTRIBUTE));
    }

    public function testRequestContextSetFromRequest(): void
    {
        $requestContext = new RequestContext('', 'GET', 'default.example.com', 'https');
        $event = $this->requestEvent(Request::create('http://site.example.com:8080/page'));

        $this->subscriber(new MaintenanceSettings(), false, $requestContext)->onKernelRequest($event);

        $this->assertSame('site.example.com', $requestContext->getHost());
        $this->assertSame('http', $requestContext->getScheme());
        $this->assertSame(8080, $requestContext->getHttpPort());
    }

    public function testProfilerInDebug(): void
    {
        $event = $this->requestEvent(Request::create('/_wdt/abc123'));

        $this->subscriber(new MaintenanceSettings(), true)->onKernelRequest($event);

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
        bool $debug = false,
        ?RequestContext $requestContext = null,
    ): MaintenanceSubscriber {
        $maintenanceMode = \Mockery::mock(MaintenanceMode::class);
        $maintenanceMode->shouldReceive('settings')->andReturn($settings);
        $maintenanceMode->shouldReceive('page')->andReturn(null);

        return new MaintenanceSubscriber(
            new MaintenanceGate($maintenanceMode),
            new MaintenancePage(new Environment(new ArrayLoader([MaintenancePage::TEMPLATE => 'rendered page']))),
            $debug,
            $requestContext,
        );
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
