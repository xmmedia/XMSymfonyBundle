<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSubscriber;

use Overblog\GraphQLBundle\Event\Events;
use Overblog\GraphQLBundle\Event\ExecutorArgumentsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class GraphQlContextInitialization implements EventSubscriberInterface
{
    /** @var RequestStack */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::PRE_EXECUTOR => 'onPreExecutor',
        ];
    }

    public function onPreExecutor(ExecutorArgumentsEvent $event): void
    {
        $context = $event->getContextValue();

        $context['request'] = $this->requestStack->getCurrentRequest();

        $event->setContextValue($context);
    }
}
