<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSourcing\Aggregate;

use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateTranslatorInterface as EventStoreAggregateTranslator;
use Xm\SymfonyBundle\Messaging\Message;

final class AggregateTranslator implements EventStoreAggregateTranslator
{
    private AggregateRootDecorator $aggregateRootDecorator;

    public function extractAggregateVersion(AggregateRoot $eventSourcedAggregateRoot): int
    {
        return $this->getAggregateRootDecorator()->extractAggregateVersion($eventSourcedAggregateRoot);
    }

    public function extractAggregateId(AggregateRoot $eventSourcedAggregateRoot): string
    {
        return $this->getAggregateRootDecorator()->extractAggregateId($eventSourcedAggregateRoot);
    }

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
    public function extractPendingStreamEvents(AggregateRoot $anEventSourcedAggregateRoot): array
    {
        return $this->getAggregateRootDecorator()->extractRecordedEvents($anEventSourcedAggregateRoot);
    }

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
     * @deprecated Likely not used.
     */
    public function setAggregateRootDecorator(AggregateRootDecorator $anAggregateRootDecorator): void
    {
        $this->aggregateRootDecorator = $anAggregateRootDecorator;
    }
}
