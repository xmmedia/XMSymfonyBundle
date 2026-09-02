<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\Service;

use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\EventStore\Exception\ProjectionNotFound;
use Prooph\EventStore\Exception\RuntimeException;
use Prooph\EventStore\Projection\ProjectionManager;
use Prooph\EventStore\Projection\ProjectionStatus;
use Prooph\EventStore\Projection\ReadModel;
use Prooph\EventStore\Projection\ReadModelProjector;
use Psr\Container\ContainerInterface;
use Xm\SymfonyBundle\Infrastructure\Service\ProjectionRunner;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class ProjectionRunnerTest extends BaseTestCase
{
    private const PROJECTION = 'fake_projection';

    public function testRun(): void
    {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('run')
            ->once()
            ->with(false);

        $this->runner($projector, [ProjectionStatus::IDLE()])
            ->run(self::PROJECTION);
    }

    /**
     * The projection status is read from the status column, but the lock is
     * acquired on the locked_until column, so another process can acquire the
     * lock after the status has been read as idle. The run must be retried,
     * not silently skipped, otherwise the read model is left stale.
     */
    public function testRunRetriesWhenLockAcquiredByAnotherProcessAfterStatusRead(): void
    {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('run')
            ->once()
            ->with(false)
            ->andThrow(new RuntimeException('Another projection process is already running'))
            ->ordered();
        $projector->shouldReceive('run')
            ->once()
            ->with(false)
            ->ordered();

        $this->runner($projector, [ProjectionStatus::IDLE(), ProjectionStatus::IDLE()])
            ->run(self::PROJECTION);
    }

    public function testRunRetriesWhenNotIdle(): void
    {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('run')
            ->once()
            ->with(false);

        $this->runner($projector, [ProjectionStatus::RUNNING(), ProjectionStatus::IDLE()])
            ->run(self::PROJECTION);
    }

    public function testRunThrowsWhenLockNeverAcquired(): void
    {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('run')
            ->times(3)
            ->with(false)
            ->andThrow(new RuntimeException('Another projection process is already running'));

        $runner = $this->runner(
            $projector,
            array_fill(0, 3, ProjectionStatus::IDLE()),
            3,
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('Another projection process is already running');

        $runner->run(self::PROJECTION);
    }

    public function testRunThrowsWhenNeverIdle(): void
    {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldNotReceive('run');

        $runner = $this->runner(
            $projector,
            array_fill(0, 3, ProjectionStatus::RUNNING()),
            3,
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageIsOrContains(
            'Projection "fake_projection" could not be run. It\'s state is "running". Attempted 3 times.',
        );

        $runner->run(self::PROJECTION);
    }

    public function testRunRethrowsOtherRuntimeExceptions(): void
    {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('run')
            ->once()
            ->with(false)
            ->andThrow(new RuntimeException('Something else went wrong'));

        $runner = $this->runner($projector, [ProjectionStatus::IDLE()]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageIsOrContains('Something else went wrong');

        $runner->run(self::PROJECTION);
    }

    public function testRunWhenProjectionNotInitialized(): void
    {
        $projector = \Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('run')
            ->once()
            ->with(false);

        $this->runner($projector, [ProjectionNotFound::withName(self::PROJECTION)])
            ->run(self::PROJECTION);
    }

    /**
     * @param array<ProjectionStatus|\Throwable> $statuses returned, in order, by fetchProjectionStatus()
     */
    private function runner(
        ReadModelProjector $projector,
        array $statuses,
        int $maxAttempts = ProjectionRunner::DEFAULT_MAX_ATTEMPTS,
    ): ProjectionRunner {
        $projectionManager = \Mockery::mock(ProjectionManager::class);
        $projectionManager->shouldReceive('createReadModelProjection')
            ->once()
            ->andReturn($projector);

        foreach ($statuses as $status) {
            $expectation = $projectionManager->shouldReceive('fetchProjectionStatus')
                ->once()
                ->with(self::PROJECTION)
                ->ordered();

            if ($status instanceof \Throwable) {
                $expectation->andThrow($status);
            } else {
                $expectation->andReturn($status);
            }
        }

        $projection = \Mockery::mock(ReadModelProjection::class);
        $projection->shouldReceive('project')
            ->once()
            ->with($projector)
            ->andReturn($projector);

        return new ProjectionRunner(
            \Mockery::mock(ProjectionManager::class),
            $this->locator($projectionManager),
            $this->locator($projection),
            $this->locator(\Mockery::mock(ReadModel::class)),
            maxAttempts: $maxAttempts,
            // don't slow the tests down with the delay between attempts
            retryDelay: 0,
        );
    }

    private function locator(object $service): ContainerInterface
    {
        $locator = \Mockery::mock(ContainerInterface::class);
        $locator->shouldReceive('has')
            ->with(self::PROJECTION)
            ->andReturn(true);
        $locator->shouldReceive('get')
            ->with(self::PROJECTION)
            ->andReturn($service);

        return $locator;
    }
}
