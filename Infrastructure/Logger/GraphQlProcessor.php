<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Logger;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Overblog\GraphQLBundle\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Adds the query of the GraphQL request that errored to the log records after it.
 * It's cleared at the start of each main request (sub-requests keep it),
 * so a long running process (eg FrankenPHP's worker mode) doesn't add it
 * to the records of later requests. It's also cleared on reset, which only
 * happens if the service is autoconfigured or tagged with kernel.reset.
 */
class GraphQlProcessor implements ProcessorInterface, EventSubscriberInterface, ResetInterface
{
    private ?string $query = null;

    public function __invoke(LogRecord $record): LogRecord
    {
        if (null !== $this->query) {
            $record->extra['query'] = $this->query;
        }

        return $record;
    }

    public function onGraphQlError(ErrorFormattingEvent $event): void
    {
        // keep the query from an earlier error if this one doesn't have it
        $this->query = $event->getError()->getSource()?->body ?? $this->query;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            $this->reset();
        }
    }

    public function reset(): void
    {
        $this->query = null;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::ERROR_FORMATTING => ['onGraphQlError', 4096],
            // early, so little is logged with the previous request's query
            KernelEvents::REQUEST    => ['onKernelRequest', 4096],
        ];
    }
}
