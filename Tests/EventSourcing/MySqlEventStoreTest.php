<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\EventSourcing;

use Prooph\Common\Messaging\MessageFactory;
use Prooph\EventStore\Exception\ConcurrencyException;
use Prooph\EventStore\Pdo\PersistenceStrategy\MySqlPersistenceStrategy;
use Prooph\EventStore\Pdo\WriteLockStrategy;
use Prooph\EventStore\StreamName;
use Xm\SymfonyBundle\EventSourcing\MySqlEventStore;
use Xm\SymfonyBundle\Test\BaseTestCase;

class MySqlEventStoreTest extends BaseTestCase
{
    public function testWriteLockNameIncludesTheDatabase(): void
    {
        $this->assertSame(
            'event_write:app_one.user',
            MySqlEventStore::writeLockName('app_one', new StreamName('user')),
        );
    }

    public function testWriteLockNameIsDifferentForEachDatabase(): void
    {
        $streamName = new StreamName('user');

        $this->assertNotSame(
            MySqlEventStore::writeLockName('app_one', $streamName),
            MySqlEventStore::writeLockName('app_two', $streamName),
        );
    }

    public function testWriteLockNameWithNoDatabase(): void
    {
        $this->assertSame('event_write:.user', MySqlEventStore::writeLockName(null, new StreamName('user')));
    }

    public function testWriteLockNameOf64CharactersIsKept(): void
    {
        // 12 for "event_write:" + 47 + 1 for the "." + 4 = 64
        $database = str_repeat('d', 47);

        $this->assertSame(
            'event_write:'.$database.'.user',
            MySqlEventStore::writeLockName($database, new StreamName('user')),
        );
    }

    public function testLongWriteLockNameIsShortenedWithAHash(): void
    {
        $database = str_repeat('d', 64);
        $streamName = new StreamName('user');

        $name = MySqlEventStore::writeLockName($database, $streamName);

        $this->assertSame(64, mb_strlen($name));
        $this->assertStringStartsWith('event_write:'.str_repeat('d', 43).'~', $name);
        $this->assertSame(
            substr(sha1('event_write:'.$database.'.user'), 0, 8),
            substr($name, -8),
        );
    }

    public function testLongWriteLockNamesThatStartTheSameAreDifferent(): void
    {
        $database = str_repeat('d', 64);

        $this->assertNotSame(
            MySqlEventStore::writeLockName($database, new StreamName('user')),
            MySqlEventStore::writeLockName($database, new StreamName('auth')),
        );
    }

    public function testAppendToLocksWithTheCurrentDatabase(): void
    {
        $statement = \Mockery::mock(\PDOStatement::class);
        $statement->shouldReceive('fetchColumn')
            ->once()
            ->andReturn('app_one');

        $connection = \Mockery::mock(\PDO::class);
        $connection->shouldReceive('query')
            ->once()
            ->with('SELECT DATABASE()')
            ->andReturn($statement);

        $persistenceStrategy = \Mockery::mock(MySqlPersistenceStrategy::class);
        $persistenceStrategy->shouldReceive('prepareData')->andReturn(['data']);
        $persistenceStrategy->shouldReceive('columnNames')->andReturn(['event_id']);
        $persistenceStrategy->shouldReceive('generateTableName')->andReturn('user_event_stream');

        // refusing the lock stops the append before it writes anything
        $writeLockStrategy = \Mockery::mock(WriteLockStrategy::class);
        $writeLockStrategy->shouldReceive('getLock')
            ->once()
            ->with('event_write:app_one.user')
            ->andReturnFalse();

        $eventStore = new MySqlEventStore(
            \Mockery::mock(MessageFactory::class),
            $connection,
            $persistenceStrategy,
            writeLockStrategy: $writeLockStrategy,
        );

        $this->expectException(ConcurrencyException::class);

        $eventStore->appendTo(new StreamName('user'), new \ArrayIterator([new \stdClass()]));
    }
}
