<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Security;

use Carbon\CarbonImmutable;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * Signs out users who've been idle for longer than the session's gc_maxlifetime.
 * PHP only removes a session when garbage collection happens to run, & doesn't check its age
 * when reading it, so without this an idle session can be used long after it should've expired.
 *
 * Sessions signed in with remember-me don't expire: they last as long as the cookie.
 *
 * On by default; disable with xm_symfony.session_expiry.enabled: false.
 */
readonly class SessionExpiry
{
    public const string LAST_ACTIVITY_KEY = '_last_activity';
    // set to false in a route's defaults so requests to it don't count as activity
    public const string EXTEND_ATTRIBUTE = '_extend_session';

    private int $lifetime;

    public function __construct(
        #[Autowire(param: 'session.storage.options')]
        array $sessionOptions,
        // the firewalls' remember-me cookie names, set by SessionExpiryPass
        private array $rememberMeCookies = [],
    ) {
        $this->lifetime = (int) $sessionOptions['gc_maxlifetime'];
    }

    /**
     * Seconds until the session expires, or null if it doesn't expire.
     */
    public function remaining(Request $request): ?int
    {
        if (!$request->hasPreviousSession() || $this->hasRememberMeCookie($request)) {
            return null;
        }

        $lastActivity = $request->getSession()->get(self::LAST_ACTIVITY_KEY);
        if (null === $lastActivity) {
            return null;
        }

        return max(0, $lastActivity + $this->lifetime - CarbonImmutable::now()->getTimestamp());
    }

    public function isExpired(Request $request): bool
    {
        return 0 === $this->remaining($request);
    }

    public function extend(Request $request): void
    {
        $request->getSession()->set(self::LAST_ACTIVITY_KEY, CarbonImmutable::now()->getTimestamp());
    }

    private function hasRememberMeCookie(Request $request): bool
    {
        foreach ($this->rememberMeCookies as $cookie) {
            if ($request->cookies->has($cookie)) {
                return true;
            }
        }

        return false;
    }
}
