<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\Logger;

use GraphQL\Error\Error;
use GraphQL\Language\Source;
use Monolog\Level;
use Monolog\LogRecord;
use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Overblog\GraphQLBundle\Event\Events;
use Xm\SymfonyBundle\Infrastructure\Logger\GraphQlProcessor;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class GraphQlProcessorTest extends BaseTestCase
{
    public function testInvokeWithoutEvent(): void
    {
        $processor = new GraphQlProcessor();

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
        );

        $result = $processor($record);

        $this->assertSame($record, $result);
        $this->assertArrayNotHasKey('query', $result->extra);
    }

    public function testInvokeWithEvent(): void
    {
        $processor = new GraphQlProcessor();

        $queryString = 'query { user { id name } }';
        $source = new Source($queryString);
        $error = new Error('Test error', null, $source);

        $event = new ErrorFormattingEvent($error, []);

        $processor->onGraphQlError($event);

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Error,
            message: 'GraphQL error',
        );

        $result = $processor($record);

        $this->assertArrayHasKey('query', $result->extra);
        $this->assertEquals($queryString, $result->extra['query']);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = GraphQlProcessor::getSubscribedEvents();

        $this->assertArrayHasKey(Events::ERROR_FORMATTING, $events);
        $this->assertEquals(['onGraphQlError', 4096], $events[Events::ERROR_FORMATTING]);
    }

    public function testOnGraphQlErrorStoresEvent(): void
    {
        $processor = new GraphQlProcessor();

        $queryString = 'mutation { addUser(name: "Test") { id } }';
        $source = new Source($queryString);
        $error = new Error('Mutation error', null, $source);

        $event = new ErrorFormattingEvent($error, []);

        $processor->onGraphQlError($event);

        $record1 = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Error,
            message: 'First error',
        );

        $result1 = $processor($record1);
        $this->assertEquals($queryString, $result1->extra['query']);

        $record2 = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Error,
            message: 'Second error',
        );

        $result2 = $processor($record2);
        $this->assertEquals($queryString, $result2->extra['query']);
    }
}
