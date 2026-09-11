<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\EventSubscriber;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Xm\SymfonyBundle\EventSubscriber\MaintenanceSubscriber;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceGate;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceMode;
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

    public function testOn(): void
    {
        $page = $this->faker()->randomHtml();
        $event = $this->requestEvent(Request::create('/'));

        $this->subscriber(new MaintenanceSettings(), $page)->onKernelRequest($event);

        $this->assertSame(Response::HTTP_SERVICE_UNAVAILABLE, $event->getResponse()->getStatusCode());
        $this->assertSame($page, $event->getResponse()->getContent());
    }

    public function testAllowed(): void
    {
        $ip = $this->faker()->ipv4();
        $request = Request::create('/', server: ['REMOTE_ADDR' => $ip]);
        $event = $this->requestEvent($request);

        $this->subscriber(new MaintenanceSettings(null, null, [$ip]))->onKernelRequest($event);

        $this->assertNull($event->getResponse());
        $this->assertTrue($request->attributes->get(MaintenanceGate::BYPASS_ATTRIBUTE));
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

    private function subscriber(?MaintenanceSettings $settings, ?string $page = null): MaintenanceSubscriber
    {
        $maintenanceMode = \Mockery::mock(MaintenanceMode::class);
        $maintenanceMode->shouldReceive('settings')->andReturn($settings);
        $maintenanceMode->shouldReceive('page')->andReturn($page);

        return new MaintenanceSubscriber(new MaintenanceGate($maintenanceMode));
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
