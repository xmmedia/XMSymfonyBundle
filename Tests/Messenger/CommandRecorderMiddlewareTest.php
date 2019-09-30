<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Messenger;

use Doctrine\DBAL\Connection;
use Mockery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Xm\SymfonyBundle\Messenger\CommandRecorderMiddleware;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeCommand;

class CommandRecorderMiddlewareTest extends BaseTestCase
{
    public function test(): void
    {
        /** @var Connection|Mockery\MockInterface $connection */
        $connection = Mockery::mock(Connection::class);
        $connection->shouldReceive('insert')
            ->once()
            ->withArgs(function ($tableName): bool {
                return 'command_log' === $tableName;
            });

        $middleware = new CommandRecorderMiddleware($connection);

        $middleware->handle(
            new Envelope(FakeCommand::perform()),
            new StackMiddleware()
        );
    }
}
