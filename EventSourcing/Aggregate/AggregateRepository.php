<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSourcing\Aggregate;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\Exception\StreamNotFound;
use Prooph\EventStore\Metadata\FieldType;
use Prooph\EventStore\Metadata\MetadataMatcher;
use Prooph\EventStore\Metadata\Operator;
use Prooph\EventStore\StreamName;
use Xm\SymfonyBundle\Messaging\Message;

class AggregateRepository
{
    protected array $identityMap = [];

    public function __construct(
        private readonly EventStore $eventStore,
        private readonly AggregateType $aggregateType,
        private readonly AggregateTranslator $aggregateTranslator,
        private StreamName|null $streamName = null,
    ) {
    }

    public function saveAggregateRoot(object $eventSourcedAggregateRoot): void
    {
        $this->assertAggregateType($eventSourcedAggregateRoot);

        $domainEvents = $this->aggregateTranslator->extractPendingStreamEvents($eventSourcedAggregateRoot);
        $aggregateId = $this->aggregateTranslator->extractAggregateId($eventSourcedAggregateRoot);
        $streamName = $this->determineStreamName($aggregateId);

        $firstEvent = reset($domainEvents);

        if (false === $firstEvent) {
            return;
        }

        $enrichedEvents = array_map(function ($event) use ($aggregateId): Message {
            return $this->enrichEventMetadata($event, $aggregateId);
        }, $domainEvents);

        $this->eventStore->appendTo($streamName, new \ArrayIterator($enrichedEvents));

        if (isset($this->identityMap[$aggregateId])) {
            unset($this->identityMap[$aggregateId]);
        }
    }

    /**
     * Returns null if no stream events can be found for aggregate root otherwise the reconstituted aggregate root.
     */
    public function getAggregateRoot(string $aggregateId): object|null
    {
        if (isset($this->identityMap[$aggregateId])) {
            return $this->identityMap[$aggregateId];
        }

        $streamName = $this->determineStreamName($aggregateId);

        $metadataMatcher = new MetadataMatcher();
        $metadataMatcher = $metadataMatcher->withMetadataMatch(
            'aggregate_type',
            Operator::EQUALS(),
            $this->aggregateType->toString(),
            FieldType::MESSAGE_PROPERTY(),
        );
        $metadataMatcher = $metadataMatcher->withMetadataMatch(
            'aggregate_id',
            Operator::EQUALS(),
            $aggregateId,
            FieldType::MESSAGE_PROPERTY(),
        );

        try {
            $streamEvents = $this->eventStore->load($streamName, 1, null, $metadataMatcher);
        } catch (StreamNotFound $e) {
            return null;
        }

        if (!$streamEvents->valid()) {
            return null;
        }

        $eventSourcedAggregateRoot = $this->aggregateTranslator->reconstituteAggregateFromHistory(
            $this->aggregateType,
            $streamEvents,
        );

        // Cache aggregate root in the identity map but without pending events
        $this->identityMap[$aggregateId] = $eventSourcedAggregateRoot;

        return $eventSourcedAggregateRoot;
    }

    public function getAggregateRootEvents(string $aggregateId, ?string $eventName = null): \Iterator
    {
        $streamName = $this->determineStreamName($aggregateId);

        $metadataMatcher = new MetadataMatcher();
        $metadataMatcher = $metadataMatcher->withMetadataMatch(
            'aggregate_type',
            Operator::EQUALS(),
            $this->aggregateType->toString(),
            FieldType::MESSAGE_PROPERTY(),
        );
        $metadataMatcher = $metadataMatcher->withMetadataMatch(
            'aggregate_id',
            Operator::EQUALS(),
            $aggregateId,
            FieldType::MESSAGE_PROPERTY(),
        );
        if ($eventName) {
            $metadataMatcher = $metadataMatcher->withMetadataMatch(
                'event_name',
                Operator::EQUALS(),
                $eventName,
                FieldType::MESSAGE_PROPERTY(),
            );
        }

        try {
            $streamEvents = $this->eventStore->load($streamName, 1, null, $metadataMatcher);
        } catch (StreamNotFound $e) {
            return new \ArrayIterator();
        }

        return $streamEvents;
    }

    public function extractAggregateVersion(object $aggregateRoot): int
    {
        return $this->aggregateTranslator->extractAggregateVersion($aggregateRoot);
    }

    /**
     * Empties the identity map. Use this if you load thousands of aggregates to free memory e.g. modulo 500.
     */
    public function clearIdentityMap(): void
    {
        $this->identityMap = [];
    }

    protected function isFirstEvent(Message $message): bool
    {
        return 1 === $message->metadata()['_aggregate_version'];
    }

    /**
     * Default stream name generation.
     * Override this method in an extending repository to provide a custom name.
     */
    protected function determineStreamName(string $aggregateId): StreamName
    {
        if (null === $this->streamName) {
            return new StreamName('event_stream');
        }

        return $this->streamName;
    }

    /**
     * Add aggregate_id and aggregate_type as metadata to $domainEvent
     * Override this method in an extending repository to add more or different metadata.
     */
    protected function enrichEventMetadata(Message $domainEvent, string $aggregateId): Message
    {
        $domainEvent = $domainEvent->withAddedMetadata('_aggregate_id', $aggregateId);
        $domainEvent = $domainEvent->withAddedMetadata('_aggregate_type', $this->aggregateType->toString());

        return $domainEvent;
    }

    protected function assertAggregateType(object $eventSourcedAggregateRoot): void
    {
        $this->aggregateType->assert($eventSourcedAggregateRoot);
    }
}
