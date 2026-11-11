<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Command;

use PHPUnit\Framework\Attributes\DataProvider;
use Prooph\EventStore\Projection\ProjectionManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Xm\SymfonyBundle\Command\ProjectionDeleteCommand;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class ProjectionDeleteCommandTest extends BaseTestCase
{
    #[DataProvider('projectionNameProvider')]
    public function testDelete(string $projectionName, string $expected): void
    {
        $projectionManager = \Mockery::mock(ProjectionManager::class);
        $projectionManager->shouldReceive('deleteProjection')
            ->once()
            ->with($expected, false);

        $tester = new CommandTester(new ProjectionDeleteCommand($projectionManager));
        $tester->execute(['projection-name' => $projectionName]);

        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Deleting projection '.$expected.' without emitted events', $tester->getDisplay());
    }

    public static function projectionNameProvider(): \Generator
    {
        yield 'without suffix' => ['user', 'user_projection'];
        yield 'with suffix'    => ['user_projection', 'user_projection'];
    }

    #[DataProvider('withEventsOptionProvider')]
    public function testDeleteWithEmittedEvents(array $option): void
    {
        $projectionManager = \Mockery::mock(ProjectionManager::class);
        $projectionManager->shouldReceive('deleteProjection')
            ->once()
            ->with('user_projection', true);

        $tester = new CommandTester(new ProjectionDeleteCommand($projectionManager));
        $tester->execute(['projection-name' => 'user'] + $option);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('Deleting projection user_projection with emitted events', $tester->getDisplay());
    }

    public static function withEventsOptionProvider(): \Generator
    {
        yield 'long option'  => [['--with-emitted-events' => true]];
        yield 'short option' => [['-w' => true]];
    }

    public function testNameEscapedInOutput(): void
    {
        $projectionManager = \Mockery::mock(ProjectionManager::class);
        $projectionManager->shouldReceive('deleteProjection')
            ->once()
            ->with('user</comment><error>_projection', false);

        $tester = new CommandTester(new ProjectionDeleteCommand($projectionManager));
        $tester->execute(['projection-name' => 'user</comment><error>']);

        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Deleting projection user</comment><error>_projection without emitted events', $tester->getDisplay());
    }
}
