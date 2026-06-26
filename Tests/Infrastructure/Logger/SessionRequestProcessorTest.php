<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\Logger;

use Monolog\Level;
use Monolog\LogRecord;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Xm\SymfonyBundle\Infrastructure\Logger\SessionRequestProcessor;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class SessionRequestProcessorTest extends BaseTestCase
{
    public function testInvokeWithNoSession(): void
    {
        $requestStack = \Mockery::mock(RequestStack::class);
        $requestStack->shouldReceive('getSession')
            ->once()
            ->andThrow(new SessionNotFoundException());

        $processor = new SessionRequestProcessor($requestStack);

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
        );

        $result = $processor($record);

        $this->assertSame($record, $result);
        $this->assertArrayNotHasKey('token', $result->extra);
    }

    public function testInvokeWithSessionNotStarted(): void
    {
        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('isStarted')
            ->once()
            ->andReturn(false);

        $requestStack = \Mockery::mock(RequestStack::class);
        $requestStack->shouldReceive('getSession')
            ->once()
            ->andReturn($session);

        $processor = new SessionRequestProcessor($requestStack);

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
        );

        $result = $processor($record);

        $this->assertSame($record, $result);
        $this->assertArrayNotHasKey('token', $result->extra);
    }

    public function testInvokeWithStartedSession(): void
    {
        $sessionId = 'abc123def456ghi789';

        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('isStarted')
            ->once()
            ->andReturn(true);
        $session->shouldReceive('getId')
            ->once()
            ->andReturn($sessionId);

        $requestStack = \Mockery::mock(RequestStack::class);
        $requestStack->shouldReceive('getSession')
            ->once()
            ->andReturn($session);

        $processor = new SessionRequestProcessor($requestStack);

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
        );

        $result = $processor($record);

        $this->assertArrayHasKey('token', $result->extra);
        $this->assertStringStartsWith('abc123de', $result->extra['token']);
        $this->assertStringContainsString('-', $result->extra['token']);
    }

    public function testInvokeWithEmptySessionId(): void
    {
        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('isStarted')
            ->once()
            ->andReturn(true);
        $session->shouldReceive('getId')
            ->once()
            ->andReturn('');

        $requestStack = \Mockery::mock(RequestStack::class);
        $requestStack->shouldReceive('getSession')
            ->once()
            ->andReturn($session);

        $processor = new SessionRequestProcessor($requestStack);

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
        );

        $result = $processor($record);

        $this->assertArrayHasKey('token', $result->extra);
        $this->assertStringStartsWith('????????', $result->extra['token']);
    }

    public function testInvokeWithShortSessionId(): void
    {
        $sessionId = 'abc';

        $session = \Mockery::mock(SessionInterface::class);
        $session->shouldReceive('isStarted')
            ->once()
            ->andReturn(true);
        $session->shouldReceive('getId')
            ->once()
            ->andReturn($sessionId);

        $requestStack = \Mockery::mock(RequestStack::class);
        $requestStack->shouldReceive('getSession')
            ->once()
            ->andReturn($session);

        $processor = new SessionRequestProcessor($requestStack);

        $record = new LogRecord(
            datetime: new \DateTimeImmutable(),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
        );

        $result = $processor($record);

        $this->assertArrayHasKey('token', $result->extra);
        $this->assertStringStartsWith('abc', $result->extra['token']);
    }
}
