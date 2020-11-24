<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Util\Tests;

use Prooph\EventStore\InMemoryEventStore;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRepository;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateTranslator;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateType;

trait AggregateRepositoryFactory
{
    private function getRepository(
        string $repositoryClass,
        string $aggregateRootClass
    ): AggregateRepository {
        $aggregateType = AggregateType::fromAggregateRootClass($aggregateRootClass);
        $aggregateTranslator = new AggregateTranslator();

        $eventStore = new InMemoryEventStore();
        $eventStore->beginTransaction();
        $eventStore->create(
            new Stream(new StreamName('event_stream'), new \ArrayIterator())
        );
        $eventStore->commit();

        return new $repositoryClass(
            $eventStore,
            $aggregateType,
            $aggregateTranslator
        );
    }
}
