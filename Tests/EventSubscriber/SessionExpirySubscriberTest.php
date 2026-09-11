<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\EventSubscriber;

use Carbon\CarbonImmutable;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\User\UserInterface;
use Xm\SymfonyBundle\EventSubscriber\SessionExpirySubscriber;
use Xm\SymfonyBundle\Security\SessionExpiry;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class SessionExpirySubscriberTest extends BaseTestCase
{
    private const int LIFETIME = 3600;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function testSubscribedEvents(): void
    {
        $subscribed = SessionExpirySubscriber::getSubscribedEvents();

        $this->assertSame(['expire', 16], $subscribed[KernelEvents::REQUEST]);
        $this->assertSame(['extend', 0], $subscribed[KernelEvents::RESPONSE]);
    }

    public function testExpireInvalidatesExpiredSession(): void
    {
        $request = $this->request(CarbonImmutable::now()->subSeconds(self::LIFETIME + 1)->getTimestamp());
        $sessionId = $request->getSession()->getId();

        $this->subscriber()->expire($this->requestEvent($request));

        $this->assertNotSame($sessionId, $request->getSession()->getId());
        $this->assertNull($request->getSession()->get(SessionExpiry::LAST_ACTIVITY_KEY));
    }

    public function testExpireKeepsActiveSession(): void
    {
        $lastActivity = CarbonImmutable::now()->subSeconds(60)->getTimestamp();
        $request = $this->request($lastActivity);

        $this->subscriber()->expire($this->requestEvent($request));

        $this->assertSame($lastActivity, $request->getSession()->get(SessionExpiry::LAST_ACTIVITY_KEY));
    }

    public function testExpireNotMainRequest(): void
    {
        $lastActivity = CarbonImmutable::now()->subSeconds(self::LIFETIME + 1)->getTimestamp();
        $request = $this->request($lastActivity);

        $this->subscriber()->expire($this->requestEvent($request, HttpKernelInterface::SUB_REQUEST));

        $this->assertSame($lastActivity, $request->getSession()->get(SessionExpiry::LAST_ACTIVITY_KEY));
    }

    public function testExtend(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::now());
        $request = $this->request(CarbonImmutable::now()->subSeconds(600)->getTimestamp());

        $this->subscriber(true)->extend($this->responseEvent($request));

        $this->assertSame(
            CarbonImmutable::now()->getTimestamp(),
            $request->getSession()->get(SessionExpiry::LAST_ACTIVITY_KEY),
        );
    }

    public function testExtendNotLoggedIn(): void
    {
        $request = $this->request(null);

        $this->subscriber(false)->extend($this->responseEvent($request));

        $this->assertNull($request->getSession()->get(SessionExpiry::LAST_ACTIVITY_KEY));
    }

    public function testExtendSkipsRouteOptedOut(): void
    {
        $lastActivity = CarbonImmutable::now()->subSeconds(600)->getTimestamp();
        $request = $this->request($lastActivity);
        $request->attributes->set(SessionExpiry::EXTEND_ATTRIBUTE, false);

        $this->subscriber()->extend($this->responseEvent($request));

        $this->assertSame($lastActivity, $request->getSession()->get(SessionExpiry::LAST_ACTIVITY_KEY));
    }

    public function testExtendSessionNotStarted(): void
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $this->subscriber()->extend($this->responseEvent($request));

        $this->assertFalse($request->getSession()->isStarted());
    }

    public function testExtendNotMainRequest(): void
    {
        $lastActivity = CarbonImmutable::now()->subSeconds(600)->getTimestamp();
        $request = $this->request($lastActivity);

        $this->subscriber()->extend($this->responseEvent($request, HttpKernelInterface::SUB_REQUEST));

        $this->assertSame($lastActivity, $request->getSession()->get(SessionExpiry::LAST_ACTIVITY_KEY));
    }

    /**
     * @param bool|null $loggedIn null when getUser() shouldn't be called
     */
    private function subscriber(?bool $loggedIn = null): SessionExpirySubscriber
    {
        $security = \Mockery::mock(Security::class);
        if (null === $loggedIn) {
            $security->shouldNotReceive('getUser');
        } else {
            $security->shouldReceive('getUser')
                ->once()
                ->andReturn($loggedIn ? \Mockery::mock(UserInterface::class) : null);
        }

        return new SessionExpirySubscriber(new SessionExpiry(['gc_maxlifetime' => self::LIFETIME]), $security);
    }

    /**
     * A request with a started session, as if the session cookie was sent.
     */
    private function request(?int $lastActivity): Request
    {
        $session = new Session(new MockArraySessionStorage());
        $session->start();
        if (null !== $lastActivity) {
            $session->set(SessionExpiry::LAST_ACTIVITY_KEY, $lastActivity);
        }

        $request = new Request();
        $request->setSession($session);
        $request->cookies->set($session->getName(), $session->getId());

        return $request;
    }

    private function requestEvent(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST): RequestEvent
    {
        return new RequestEvent(\Mockery::mock(HttpKernelInterface::class), $request, $type);
    }

    private function responseEvent(Request $request, int $type = HttpKernelInterface::MAIN_REQUEST): ResponseEvent
    {
        return new ResponseEvent(\Mockery::mock(HttpKernelInterface::class), $request, $type, new Response());
    }
}
