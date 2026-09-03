<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Prooph\EventStore\Projection\ReadModelProjector;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\EventStore\Projection\AbstractReadModel;

trait ProjectionReadModel
{
    private function createReadModelMock(
        string $eventStream,
        AggregateChanged $event,
        AbstractReadModel $readModel,
    ): ReadModelProjector|\Mockery\MockInterface {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with($eventStream)
            ->andReturnSelf();
        $projector->shouldReceive('when')
            ->once()
            ->andReturnUsing(function ($handlers) use ($event, $readModel, $projector) {
                $this->assertArrayHasKey($event::class, $handlers);
                $handler = $handlers[$event::class];

                $projectorMock = \Mockery::mock(ReadModelProjector::class);
                $projectorMock->shouldReceive('readModel')
                    ->andReturn($readModel);

                $handler->call($projectorMock, [], $event);

                return $projector;
            });

        return $projector;
    }

    private function getReadModelStack(AbstractReadModel $readModel): mixed
    {
        // The bundle read model shadows the Prooph parent's private stack
        $reflection = new \ReflectionClass(AbstractReadModel::class);
        $stackProperty = $reflection->getProperty('stack');

        return $stackProperty->getValue($readModel);
    }
}
