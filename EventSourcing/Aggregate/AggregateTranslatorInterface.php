<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSourcing\Aggregate;

use Xm\SymfonyBundle\Messaging\Message;

interface AggregateTranslatorInterface
{
    public function extractAggregateVersion(AggregateRoot $eventSourcedAggregateRoot): int;

    public function extractAggregateId(AggregateRoot $eventSourcedAggregateRoot): string;

    public function reconstituteAggregateFromHistory(
        AggregateType $aggregateType,
        \Iterator $historyEvents,
    ): AggregateRoot;

    /**
     * @return Message[]
     */
    public function extractPendingStreamEvents(AggregateRoot $eventSourcedAggregateRoot): array;

    public function replayStreamEvents(AggregateRoot $eventSourcedAggregateRoot, \Iterator $events): void;
}
