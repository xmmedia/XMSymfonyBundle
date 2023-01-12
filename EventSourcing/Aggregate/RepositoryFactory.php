<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSourcing\Aggregate;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\StreamName;

class RepositoryFactory
{
    public static function create(
        string $repositoryClass,
        EventStore $eventStore,
        string $aggregateType,
        AggregateTranslator $aggregateTranslator,
        string $streamName = null,
    ) {
        return new $repositoryClass(
            $eventStore,
            AggregateType::fromAggregateRootClass($aggregateType),
            $aggregateTranslator,
            $streamName ? new StreamName($streamName) : null,
        );
    }
}
