<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\Logger;

use GraphQL\Error\Error;
use GraphQL\Language\Source;
use Monolog\Level;
use Monolog\LogRecord;
use Overblog\GraphQLBundle\Event\ErrorFormattingEvent;
use Overblog\GraphQLBundle\Event\Events;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
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

    public function testInvokeWithEventButNullSource(): void
    {
        $processor = new GraphQlProcessor();

        $error = new Error('Test error');

        $event = new ErrorFormattingEvent($error, []);

        $processor->onGraphQlError($event);

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Error,
            message: 'GraphQL error',
        );

        $result = $processor($record);

        $this->assertArrayNotHasKey('query', $result->extra);
    }

    public function testGetSubscribedEvents(): void
    {
        $events = GraphQlProcessor::getSubscribedEvents();

        $this->assertArrayHasKey(Events::ERROR_FORMATTING, $events);
        $this->assertEquals(['onGraphQlError', 4096], $events[Events::ERROR_FORMATTING]);
        $this->assertArrayHasKey(KernelEvents::REQUEST, $events);
        $this->assertEquals(['onKernelRequest', 4096], $events[KernelEvents::REQUEST]);
    }

    public function testOnGraphQlErrorKeepsQueryForLaterRecords(): void
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

    public function testMainRequestClearsQuery(): void
    {
        $processor = $this->processorWithQuery('query { user { id } }');

        $processor->onKernelRequest($this->requestEvent(HttpKernelInterface::MAIN_REQUEST));

        $this->assertArrayNotHasKey('query', $processor($this->record())->extra);
    }

    public function testSubRequestKeepsQuery(): void
    {
        $processor = $this->processorWithQuery('query { user { id } }');

        $processor->onKernelRequest($this->requestEvent(HttpKernelInterface::SUB_REQUEST));

        $this->assertSame('query { user { id } }', $processor($this->record())->extra['query']);
    }

    public function testResetClearsQuery(): void
    {
        $processor = $this->processorWithQuery('query { user { id } }');

        $processor->reset();

        $this->assertArrayNotHasKey('query', $processor($this->record())->extra);
    }

    public function testLaterErrorReplacesQuery(): void
    {
        $processor = $this->processorWithQuery('query { first }');

        $processor->onGraphQlError(new ErrorFormattingEvent(new Error('Second', null, new Source('query { second }')), []));

        $this->assertSame('query { second }', $processor($this->record())->extra['query']);
    }

    public function testLaterErrorWithoutSourceKeepsQuery(): void
    {
        $processor = $this->processorWithQuery('query { first }');

        $processor->onGraphQlError(new ErrorFormattingEvent(new Error('Second'), []));

        $this->assertSame('query { first }', $processor($this->record())->extra['query']);
    }

    public function testQueryAfterNewRequest(): void
    {
        $processor = $this->processorWithQuery('query { first }');

        $processor->onKernelRequest($this->requestEvent(HttpKernelInterface::MAIN_REQUEST));
        $processor->onGraphQlError(new ErrorFormattingEvent(new Error('Second', null, new Source('query { second }')), []));

        $this->assertSame('query { second }', $processor($this->record())->extra['query']);
    }

    private function processorWithQuery(string $query): GraphQlProcessor
    {
        $processor = new GraphQlProcessor();
        $processor->onGraphQlError(new ErrorFormattingEvent(new Error('Error', null, new Source($query)), []));

        return $processor;
    }

    private function requestEvent(int $requestType): RequestEvent
    {
        return new RequestEvent(\Mockery::mock(HttpKernelInterface::class), new Request(), $requestType);
    }

    private function record(): LogRecord
    {
        return new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Error,
            message: 'Error',
        );
    }
}
