<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSubscriber;

use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Xm\SymfonyBundle\Security\SessionExpiry;

readonly class SessionExpirySubscriber implements EventSubscriberInterface
{
    public function __construct(private SessionExpiry $sessionExpiry, private Security $security)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            // before the firewall (8) so an expired session is never authenticated
            KernelEvents::REQUEST  => ['expire', 16],
            // before the session is saved (-1000)
            KernelEvents::RESPONSE => ['extend', 0],
        ];
    }

    public function expire(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($this->sessionExpiry->isExpired($event->getRequest())) {
            $event->getRequest()->getSession()->invalidate();
        }
    }

    /**
     * Every request by a signed in user extends their session.
     */
    public function extend(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        // eg checking how long is left mustn't count as activity
        if (false === $request->attributes->get(SessionExpiry::EXTEND_ATTRIBUTE, true)) {
            return;
        }

        // don't start a session just to extend it
        if (!$request->hasSession() || !$request->getSession()->isStarted()) {
            return;
        }

        if (null === $this->security->getUser()) {
            return;
        }

        $this->sessionExpiry->extend($request);
    }
}
