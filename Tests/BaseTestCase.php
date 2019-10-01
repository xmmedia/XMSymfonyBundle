<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRoot;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateTranslator;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateType;
use Xm\SymfonyBundle\Model\ValueObject;

class BaseTestCase extends \PHPUnit\Framework\TestCase
{
    use MockeryPHPUnitIntegration;
    use UsesFaker;

    /** @var AggregateTranslator */
    private $aggregateTranslator;

    protected function assertRecordedEvent(
        string $eventName,
        array $payload,
        array $events,
        $assertNotRecorded = false
    ): void {
        $isRecorded = false;

        foreach ($events as $event) {
            if (null === $event) {
                continue;
            }

            if ($event instanceof $eventName) {
                $isRecorded = true;

                if (!$assertNotRecorded) {
                    $this->assertEquals(
                        $payload,
                        $event->payload(),
                        sprintf(
                            'Payload of recorded event %s does not match with expected payload.',
                            $eventName
                        )
                    );
                }
            }
        }

        if ($assertNotRecorded) {
            $this->assertFalse(
                $isRecorded,
                sprintf('Event %s was recorded.', $eventName)
            );
        } else {
            $this->assertTrue(
                $isRecorded,
                sprintf('Event %s was not recorded.', $eventName)
            );
        }
    }

    protected function assertNotRecordedEvent(string $eventName, array $events): void
    {
        $this->assertRecordedEvent($eventName, [], $events, true);
    }

    protected function assertUuid(string $uuid): void
    {
        try {
            \Webmozart\Assert\Assert::uuid($uuid);
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(false, sprintf('The "%s" is not a UUID.', $uuid));
        }
    }

    protected function assertEqualsOrNull($expected, $actual): void
    {
        if (null !== $expected) {
            $this->assertEquals($expected, $actual);
        } else {
            $this->assertNull($actual);
        }
    }

    protected function assertSameValueAs(
        ValueObject $expected,
        ValueObject $actual
    ): void {
        $this->assertTrue($expected->sameValueAs($actual));
    }

    protected function assertSameValueAsOrNull(
        ?ValueObject $expected,
        ?ValueObject $actual
    ): void {
        if (null !== $expected) {
            $this->assertSameValueAs($expected, $actual);
        } else {
            $this->assertNull($actual);
        }
    }

    protected function assertHasAllResolverMethods(AliasedInterface $resolver): void
    {
        foreach ($resolver::getAliases() as $method => $alias) {
            $this->assertTrue(
                method_exists($resolver, $method),
                'Resolver method "'.$method.'" is missing.'
            );
        }
    }

    protected function popRecordedEvent(AggregateRoot $aggregateRoot): array
    {
        return $this->getAggregateTranslator()
            ->extractPendingStreamEvents($aggregateRoot);
    }

    /**
     * @return object
     */
    protected function reconstituteAggregateFromHistory(
        string $aggregateRootClass,
        array $events
    ) {
        return $this->getAggregateTranslator()->reconstituteAggregateFromHistory(
            AggregateType::fromAggregateRootClass($aggregateRootClass),
            new \ArrayIterator($events)
        );
    }

    private function getAggregateTranslator(): AggregateTranslator
    {
        if (null === $this->aggregateTranslator) {
            $this->aggregateTranslator = new AggregateTranslator();
        }

        return $this->aggregateTranslator;
    }

    protected function getCommandBusMock(
        ?string $commandClass
    ): MessageBusInterface {
        $commandBus = Mockery::mock(MessageBusInterface::class);

        if (null !== $commandClass) {
            $commandBus->shouldReceive('dispatch')
                ->once()
                ->with(Mockery::type($commandClass))
                ->andReturn(new Envelope(new \stdClass()));
        }

        return $commandBus;
    }
}
