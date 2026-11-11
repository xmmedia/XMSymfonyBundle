<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Command;

use Prooph\EventStore\Projection\ProjectionStatus;
use Prooph\EventStore\Projection\ReadModelProjector;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;
use Xm\SymfonyBundle\Command\ProjectionRunCommand;
use Xm\SymfonyBundle\Infrastructure\Service\ProjectionRunner;
use Xm\SymfonyBundle\Tests\BaseTestCase;

/**
 * The signal tests send real signals to this process, the way a supervisor would.
 */
class ProjectionRunCommandTest extends BaseTestCase
{
    /** @var \Closure the handler in place before the command runs, which stops a stray signal killing the tests */
    private \Closure $previousHandler;

    protected function setUp(): void
    {
        parent::setUp();

        if (!\function_exists('pcntl_signal') || !\function_exists('posix_kill')) {
            return;
        }

        $this->previousHandler = static function (): void {
        };
        pcntl_signal(\SIGTERM, $this->previousHandler);
        pcntl_async_signals(true);
    }

    protected function tearDown(): void
    {
        if (\function_exists('pcntl_signal')) {
            pcntl_signal_dispatch();
            pcntl_signal(\SIGTERM, \SIG_DFL);
            pcntl_async_signals(false);
        }

        parent::tearDown();
    }

    public function testProjectionNameOrRunAllRequired(): void
    {
        $projectionRunner = \Mockery::mock(ProjectionRunner::class);
        $projectionRunner->shouldNotReceive('configure');
        $projectionRunner->shouldNotReceive('getAllProjectionNames');

        $tester = new CommandTester(new ProjectionRunCommand($projectionRunner));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('A projection name or --run-all to run all projections is required.');

        $tester->execute([]);
    }

    public function testRun(): void
    {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('run')->once()->with(false);
        $projector->shouldNotReceive('stop');

        $tester = new CommandTester(new ProjectionRunCommand($this->runner(['user_projection' => $projector])));
        $tester->execute(['projection-name' => 'user', '--run-once' => true]);

        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Projection user_projection completed.', $tester->getDisplay());
    }

    public function testSignalOnlyHandledWhereProjectorDispatches(): void
    {
        $this->requireSignals();

        $stopped = false;

        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('run')
            ->once()
            ->with(true)
            ->andReturnUsing(function () use (&$stopped): void {
                posix_kill(getmypid(), \SIGTERM);

                // handled synchronously, so not part way through an event
                $this->assertFalse($stopped);
                $this->assertFalse(pcntl_async_signals());

                // as the projector does between events
                pcntl_signal_dispatch();

                $this->assertTrue($stopped);
            });
        $projector->shouldReceive('stop')
            ->once()
            ->andReturnUsing(static function () use (&$stopped): void {
                $stopped = true;
            });

        $tester = new CommandTester(new ProjectionRunCommand($this->runner(['user_projection' => $projector])));
        $tester->execute(['projection-name' => 'user']);

        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Projection user_projection stopped.', $tester->getDisplay());
        $this->assertStringNotContainsString('completed', $tester->getDisplay());
    }

    public function testOnlyFirstSignalStopsProjector(): void
    {
        $this->requireSignals();

        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('run')
            ->once()
            ->andReturnUsing(static function (): void {
                posix_kill(getmypid(), \SIGTERM);
                pcntl_signal_dispatch();
                posix_kill(getmypid(), \SIGINT);
                pcntl_signal_dispatch();
            });
        $projector->shouldReceive('stop')->once();

        $tester = new CommandTester(new ProjectionRunCommand($this->runner(['user_projection' => $projector])));
        $tester->execute(['projection-name' => 'user']);

        $this->assertSame(1, substr_count($tester->getDisplay(), 'Projection user_projection stopped.'));
    }

    /**
     * Symfony's Application reuses the command, so a stop in one run mustn't carry over.
     */
    public function testStopDoesNotCarryOverToNextExecution(): void
    {
        $this->requireSignals();

        $first = \Mockery::mock(ReadModelProjector::class);
        $first->shouldReceive('run')
            ->once()
            ->andReturnUsing(static function (): void {
                posix_kill(getmypid(), \SIGTERM);
                pcntl_signal_dispatch();
            });
        $first->shouldReceive('stop')->once();

        $second = \Mockery::mock(ReadModelProjector::class);
        $second->shouldReceive('run')->once();
        $second->shouldNotReceive('stop');

        $projectionRunner = \Mockery::mock(ProjectionRunner::class);
        $projectionRunner->shouldReceive('configure')
            ->twice()
            ->with('user_projection', \Mockery::type('array'))
            ->andReturn($first, $second);
        $projectionRunner->shouldReceive('state')
            ->andReturn(ProjectionStatus::IDLE());

        $tester = new CommandTester(new ProjectionRunCommand($projectionRunner));
        $tester->execute(['projection-name' => 'user']);
        $tester->execute(['projection-name' => 'user']);

        $this->assertStringContainsString('Projection user_projection completed.', $tester->getDisplay());
    }

    public function testSignalHandlingRestoredAfterRun(): void
    {
        $this->requireSignals();

        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('run')
            ->once()
            ->andReturnUsing(function (): void {
                $this->assertNotSame($this->previousHandler, pcntl_signal_get_handler(\SIGTERM));
            });

        $tester = new CommandTester(new ProjectionRunCommand($this->runner(['user_projection' => $projector])));
        $tester->execute(['projection-name' => 'user', '--run-once' => true]);

        $this->assertSame($this->previousHandler, pcntl_signal_get_handler(\SIGTERM));
        $this->assertTrue(pcntl_async_signals());
    }

    public function testSignalStopsRunAll(): void
    {
        $this->requireSignals();

        $first = \Mockery::mock(ReadModelProjector::class);
        $first->shouldReceive('run')
            ->once()
            ->with(false)
            ->andReturnUsing(static function (): void {
                posix_kill(getmypid(), \SIGTERM);
                pcntl_signal_dispatch();
            });
        $first->shouldReceive('stop')->once();

        $tester = new CommandTester(new ProjectionRunCommand($this->runAllRunner($first)));
        $tester->execute(['--run-all' => true]);

        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Projection first_projection stopped.', $tester->getDisplay());
        $this->assertStringNotContainsString('second_projection', $tester->getDisplay());
    }

    /**
     * A signal after the projector's last dispatch doesn't stop the finished
     * projector, but does stop the projections after it.
     */
    public function testSignalAfterProjectorFinishedStopsRunAll(): void
    {
        $this->requireSignals();

        $first = \Mockery::mock(ReadModelProjector::class);
        $first->shouldReceive('run')
            ->once()
            ->with(false)
            ->andReturnUsing(static function (): void {
                posix_kill(getmypid(), \SIGTERM);
            });
        $first->shouldNotReceive('stop');

        $tester = new CommandTester(new ProjectionRunCommand($this->runAllRunner($first)));
        $tester->execute(['--run-all' => true]);

        $tester->assertCommandIsSuccessful();
        $this->assertStringNotContainsString('stopped', $tester->getDisplay());
        $this->assertStringNotContainsString('second_projection', $tester->getDisplay());
    }

    private function requireSignals(): void
    {
        if (!\function_exists('pcntl_signal') || !\function_exists('posix_kill')) {
            $this->markTestSkipped('Requires the pcntl & posix extensions.');
        }
    }

    /**
     * @param array<string, ReadModelProjector> $projectors by projection name
     */
    private function runner(array $projectors): ProjectionRunner&\Mockery\MockInterface
    {
        $projectionRunner = \Mockery::mock(ProjectionRunner::class);
        foreach ($projectors as $name => $projector) {
            $projectionRunner->shouldReceive('configure')
                ->once()
                ->with($name, \Mockery::type('array'))
                ->andReturn($projector);
        }
        $projectionRunner->shouldReceive('state')
            ->andReturn(ProjectionStatus::IDLE());

        return $projectionRunner;
    }

    /**
     * Only the first projection can be configured: configuring the second fails the test.
     */
    private function runAllRunner(ReadModelProjector $first): ProjectionRunner
    {
        $projectionRunner = $this->runner(['first_projection' => $first]);
        $projectionRunner->shouldReceive('getAllProjectionNames')
            ->once()
            ->andReturn(['first_projection', 'second_projection']);

        return $projectionRunner;
    }
}
