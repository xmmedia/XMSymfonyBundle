<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSourcing;

use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Messaging\DomainEvent;

class AggregateChanged extends DomainEvent
{
    protected array $payload = [];

    public static function occur(string $aggregateId, array $payload = []): static
    {
        return new static($aggregateId, $payload);
    }

    protected function __construct(
        string $aggregateId,
        array $payload,
        array $metadata = [],
    ) {
        // Metadata needs to be set before setAggregateId and setVersion is called
        $this->metadata = $metadata;
        $this->setAggregateId($aggregateId);
        $this->setVersion($metadata['_aggregate_version'] ?? 1);
        $this->setPayload($payload);
        $this->init();
    }

    public function aggregateId(): string
    {
        return $this->metadata['_aggregate_id'];
    }

    /**
     * Return message payload as array.
     *
     * The payload should only contain scalar types and sub arrays.
     * The payload is normally passed to json_encode to persist the message or
     * push it into a message queue.
     */
    public function payload(): array
    {
        return $this->payload;
    }

    public function version(): int
    {
        return $this->metadata['_aggregate_version'];
    }

    public function withVersion(int $version): self
    {
        $self = clone $this;
        $self->setVersion($version);

        return $self;
    }

    protected function setAggregateId(string $aggregateId): void
    {
        Assert::notEmpty($aggregateId);

        $this->metadata['_aggregate_id'] = $aggregateId;
    }

    protected function setVersion(int $version): void
    {
        $this->metadata['_aggregate_version'] = $version;
    }

    /**
     * This method is called when message is instantiated named constructor fromArray.
     */
    protected function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }
}
