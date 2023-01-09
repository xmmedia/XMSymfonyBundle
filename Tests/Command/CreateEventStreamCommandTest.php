<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Command;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream;
use Symfony\Component\Console\Tester\CommandTester;
use Xm\SymfonyBundle\Command\CreateEventStreamCommand;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class CreateEventStreamCommandTest extends BaseTestCase
{
    public function test(): void
    {
        $streamName = 'user';

        $eventStore = \Mockery::mock(EventStore::class);
        $eventStore->shouldReceive('create')
            ->withArgs(function ($stream) use ($streamName): bool {
                /* @var Stream $stream */
                $this->assertInstanceOf(Stream::class, $stream);
                $this->assertEquals($streamName, $stream->streamName()->toString());

                return true;
            });

        $command = new CreateEventStreamCommand($eventStore);

        $commandTester = new CommandTester($command);
        $commandTester->execute(['stream_name' => $streamName]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Event stream "'.$streamName.'" was created successfully.', $output);
    }
}
