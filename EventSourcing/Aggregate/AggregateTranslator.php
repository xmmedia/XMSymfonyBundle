<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSourcing\Aggregate;

use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateTranslatorInterface as EventStoreAggregateTranslator;
use Xm\SymfonyBundle\Messaging\Message;

final class AggregateTranslator implements EventStoreAggregateTranslator
{
    private AggregateRootDecorator $aggregateRootDecorator;

    #[\Override]
    public function extractAggregateVersion(AggregateRoot $eventSourcedAggregateRoot): int
    {
        return $this->getAggregateRootDecorator()->extractAggregateVersion($eventSourcedAggregateRoot);
    }

    #[\Override]
    public function extractAggregateId(AggregateRoot $eventSourcedAggregateRoot): string
    {
        return $this->getAggregateRootDecorator()->extractAggregateId($eventSourcedAggregateRoot);
    }

    #[\Override]
    public function reconstituteAggregateFromHistory(
        AggregateType $aggregateType,
        \Iterator $historyEvents,
    ): AggregateRoot {
        if (!$aggregateRootClass = $aggregateType->mappedClass()) {
            $aggregateRootClass = $aggregateType->toString();
        }

        return $this->getAggregateRootDecorator()
            ->fromHistory($aggregateRootClass, $historyEvents);
    }

    /**
     * @return Message[]
     */
    #[\Override]
    public function extractPendingStreamEvents(AggregateRoot $anEventSourcedAggregateRoot): array
    {
        return $this->getAggregateRootDecorator()->extractRecordedEvents($anEventSourcedAggregateRoot);
    }

    #[\Override]
    public function replayStreamEvents(AggregateRoot $anEventSourcedAggregateRoot, \Iterator $events): void
    {
        $this->getAggregateRootDecorator()->replayStreamEvents($anEventSourcedAggregateRoot, $events);
    }

    public function getAggregateRootDecorator(): AggregateRootDecorator
    {
        if (!isset($this->aggregateRootDecorator)) {
            $this->aggregateRootDecorator = AggregateRootDecorator::newInstance();
        }

        return $this->aggregateRootDecorator;
    }

    /**
     * @deprecated likely not used
     */
    public function setAggregateRootDecorator(AggregateRootDecorator $anAggregateRootDecorator): void
    {
        $this->aggregateRootDecorator = $anAggregateRootDecorator;
    }
}
