<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Doctrine;

use Doctrine\DBAL\Connection;
use Xm\SymfonyBundle\Doctrine\PdoProvider;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class PdoProviderTest extends BaseTestCase
{
    public function testInvokeReturnsPdo(): void
    {
        $pdo = \Mockery::mock(\PDO::class);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('getNativeConnection')
            ->once()
            ->andReturn($pdo);

        $provider = new PdoProvider($connection);
        $result = $provider();

        $this->assertSame($pdo, $result);
    }

    public function testInvokeThrowsExceptionForNonPdoConnection(): void
    {
        $nonPdoConnection = new \stdClass();

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('getNativeConnection')
            ->once()
            ->andReturn($nonPdoConnection);

        $provider = new PdoProvider($connection);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expecting \PDO, but got stdClass');

        $provider();
    }

    public function testInvokeIsCallable(): void
    {
        $pdo = \Mockery::mock(\PDO::class);

        $connection = \Mockery::mock(Connection::class);
        $connection->shouldReceive('getNativeConnection')
            ->once()
            ->andReturn($pdo);

        $provider = new PdoProvider($connection);

        $this->assertIsCallable($provider);
        $this->assertSame($pdo, $provider());
    }
}
