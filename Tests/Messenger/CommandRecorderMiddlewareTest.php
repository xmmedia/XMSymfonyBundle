<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Messenger;

use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;
use Xm\SymfonyBundle\Messenger\CommandRecorderMiddleware;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeCommand;

class CommandRecorderMiddlewareTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        /** @var Connection|\Mockery\MockInterface $connection */
        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('insert')
            ->once()
            ->withArgs(function ($tableName): bool {
                return 'command_log' === $tableName;
            });

        $requestInfoProvider = \Mockery::mock(RequestInfoProvider::class);

        $middleware = new CommandRecorderMiddleware($connection, $requestInfoProvider);
        $requestInfoProvider->shouldReceive('ipAddress')
            ->once()
            ->andReturn($faker->ipv4());
        $requestInfoProvider->shouldReceive('userAgent')
            ->once()
            ->andReturn($faker->userAgent());

        $middleware->handle(
            new Envelope(FakeCommand::perform()),
            new StackMiddleware(),
        );
    }
}
