<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Security;

use Carbon\CarbonImmutable;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Xm\SymfonyBundle\Security\SessionExpiry;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class SessionExpiryTest extends BaseTestCase
{
    private const int LIFETIME = 3600;

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    public function testRemaining(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::now());
        $request = $this->requestWithSession(CarbonImmutable::now()->subSeconds(600)->getTimestamp());

        $this->assertSame(3000, $this->sessionExpiry()->remaining($request));
        $this->assertFalse($this->sessionExpiry()->isExpired($request));
    }

    public function testRemainingExpired(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::now());
        $request = $this->requestWithSession(
            CarbonImmutable::now()->subSeconds(self::LIFETIME + $this->faker()->numberBetween(0, 1000))
                ->getTimestamp(),
        );

        $this->assertSame(0, $this->sessionExpiry()->remaining($request));
        $this->assertTrue($this->sessionExpiry()->isExpired($request));
    }

    public function testRemainingNoPreviousSession(): void
    {
        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $this->assertNull($this->sessionExpiry()->remaining($request));
        $this->assertFalse($this->sessionExpiry()->isExpired($request));
    }

    public function testRemainingNoLastActivity(): void
    {
        $this->assertNull($this->sessionExpiry()->remaining($this->requestWithSession(null)));
    }

    public function testRemainingRememberMe(): void
    {
        $cookie = $this->faker()->slug();
        $request = $this->requestWithSession(
            CarbonImmutable::now()->subSeconds(self::LIFETIME * 2)->getTimestamp(),
        );
        $request->cookies->set($cookie, $this->faker()->password());

        $sessionExpiry = new SessionExpiry(['gc_maxlifetime' => self::LIFETIME], [$this->faker()->word(), $cookie]);

        $this->assertNull($sessionExpiry->remaining($request));
        $this->assertFalse($sessionExpiry->isExpired($request));
    }

    public function testRemainingOtherCookie(): void
    {
        $request = $this->requestWithSession(
            CarbonImmutable::now()->subSeconds(self::LIFETIME * 2)->getTimestamp(),
        );
        $request->cookies->set('other_'.$this->faker()->slug(), $this->faker()->password());

        $sessionExpiry = new SessionExpiry(['gc_maxlifetime' => self::LIFETIME], [$this->faker()->slug()]);

        $this->assertTrue($sessionExpiry->isExpired($request));
    }

    public function testExtend(): void
    {
        CarbonImmutable::setTestNow(CarbonImmutable::now());
        $request = $this->requestWithSession(CarbonImmutable::now()->subSeconds(600)->getTimestamp());

        $this->sessionExpiry()->extend($request);

        $this->assertSame(
            CarbonImmutable::now()->getTimestamp(),
            $request->getSession()->get(SessionExpiry::LAST_ACTIVITY_KEY),
        );
        $this->assertSame(self::LIFETIME, $this->sessionExpiry()->remaining($request));
    }

    private function sessionExpiry(): SessionExpiry
    {
        return new SessionExpiry(['gc_maxlifetime' => self::LIFETIME]);
    }

    private function requestWithSession(?int $lastActivity): Request
    {
        $session = new Session(new MockArraySessionStorage());
        if (null !== $lastActivity) {
            $session->set(SessionExpiry::LAST_ACTIVITY_KEY, $lastActivity);
        }

        $request = new Request();
        $request->setSession($session);
        $request->cookies->set($session->getName(), $this->faker()->uuid());

        return $request;
    }
}
