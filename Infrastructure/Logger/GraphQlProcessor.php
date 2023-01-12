<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Logger;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;
use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Overblog\GraphQLBundle\Event\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GraphQlProcessor implements ProcessorInterface, EventSubscriberInterface
{
    private ErrorFormattingEvent $event;

    public function __invoke(LogRecord $record): LogRecord
    {
        if (isset($this->event)) {
            $record->extra['query'] = $this->event->getError()->getSource()->body;
        }

        return $record;
    }

    public function onGraphQlError(ErrorFormattingEvent $event): void
    {
        $this->event = $event;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::ERROR_FORMATTING => ['onGraphQlError', 4096],
        ];
    }
}
