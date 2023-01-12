<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSourcing\Aggregate;

class AggregateType
{
    protected string|null $aggregateType;
    protected array $mapping = [];

    /**
     * Use this factory when aggregate type should be detected based on given aggregate root.
     *
     * @throws Exception\AggregateTypeException
     */
    public static function fromAggregateRoot(object $eventSourcedAggregateRoot): self
    {
        if (!\is_object($eventSourcedAggregateRoot)) {
            throw new Exception\AggregateTypeException(sprintf('Aggregate root must be an object but type of %s given', \gettype($eventSourcedAggregateRoot)));
        }

        if ($eventSourcedAggregateRoot instanceof AggregateTypeProvider) {
            return $eventSourcedAggregateRoot->aggregateType();
        }

        $self = new static();
        $self->aggregateType = \get_class($eventSourcedAggregateRoot);

        return $self;
    }

    /**
     * Use this factory when aggregate type equals to aggregate root class
     * The factory makes sure that the aggregate root class exists.
     *
     * @throws Exception\InvalidArgumentException
     */
    public static function fromAggregateRootClass(string $aggregateRootClass): self
    {
        if (!class_exists($aggregateRootClass)) {
            throw new Exception\InvalidArgumentException(sprintf('Aggregate root class %s can not be found', $aggregateRootClass));
        }

        $self = new static();
        $self->aggregateType = $aggregateRootClass;

        return $self;
    }

    /**
     * Use this factory when the aggregate type is not equal to the aggregate root class.
     *
     * @throws Exception\InvalidArgumentException
     */
    public static function fromString(string $aggregateTypeString): self
    {
        if (empty($aggregateTypeString)) {
            throw new Exception\InvalidArgumentException('AggregateType must be a non empty string');
        }

        $self = new static();
        $self->aggregateType = $aggregateTypeString;

        return $self;
    }

    public static function fromMapping(array $mapping): self
    {
        $self = new static();
        $self->mapping = $mapping;

        return $self;
    }

    private function __construct()
    {
    }

    public function mappedClass(): ?string
    {
        return empty($this->mapping) ? null : current($this->mapping);
    }

    public function toString(): string
    {
        return empty($this->mapping) ? $this->aggregateType : key($this->mapping);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @throws Exception\AggregateTypeException
     */
    public function assert(object $aggregateRoot): void
    {
        $otherAggregateType = self::fromAggregateRoot($aggregateRoot);

        if (!$this->equals($otherAggregateType)) {
            throw new Exception\AggregateTypeException(sprintf('Aggregate types must be equal. %s != %s', $this->toString(), $otherAggregateType->toString()));
        }
    }

    public function equals(self $other): bool
    {
        if (!$aggregateTypeString = $this->mappedClass()) {
            $aggregateTypeString = $this->toString();
        }

        return $aggregateTypeString === $other->toString() || $aggregateTypeString === $other->mappedClass();
    }
}
