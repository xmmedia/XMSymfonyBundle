<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Service;

use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\EventStore\Projection\ProjectionManager;
use Prooph\EventStore\Projection\ProjectionStatus;
use Prooph\EventStore\Projection\ReadModel;
use Prooph\EventStore\Projection\ReadModelProjector;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ProjectionRunner
{
    public const DEFAULT_MAX_ATTEMPTS = 50;
    /** 1/2 second */
    public const DEFAULT_RETRY_DELAY = 500000;

    private string $projectionName;
    private ProjectionManager $projectionManager;
    private ReadModelProjector $projector;

    public function __construct(
        private readonly ProjectionManager $projectionsManager,
        private readonly ContainerInterface $projectionManagerForProjectionsLocator,
        private readonly ContainerInterface $projectionsLocator,
        private readonly ContainerInterface $projectionReadModelLocator,
        private readonly ?LoggerInterface $logger = null,
        private readonly int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS,
        // in microseconds
        private readonly int $retryDelay = self::DEFAULT_RETRY_DELAY,
    ) {
    }

    public function run(
        string $projectionName,
        bool $keepRunning = false,
        array $readModelProjectionOptions = [],
    ): void {
        $this->projectionName = $projectionName;

        $this->configure($projectionName, $readModelProjectionOptions);

        try {
            $ranSuccessfully = false;
            $attempts = 0;
            do {
                if ($attempts > 0) {
                    usleep($this->retryDelay);
                }

                $state = $this->state();
                ++$attempts;

                if ($state->is(ProjectionStatus::IDLE())) {
                    try {
                        $this->projector->run($keepRunning);
                        $ranSuccessfully = true;
                    } catch (\Prooph\EventStore\Exception\RuntimeException $e) {
                        // the status is only checked against the status column,
                        // while the lock is acquired on the locked_until column,
                        // so another process can acquire the lock between the two:
                        // retry until the projection can be run
                        if ($attempts >= $this->maxAttempts || 'Another projection process is already running' !== $e->getMessage()) {
                            throw $e;
                        }
                    }
                }

                if ($attempts > 1 && 0 === $attempts % 5 && $this->logger) {
                    $this->logger->warning(
                        \sprintf(
                            'Projection "%s" could not be run. When checked, it\'s state was "%s". Attempted to run %d times.',
                            $projectionName,
                            $state->getValue(),
                            $attempts,
                        ),
                    );
                }
            } while (!$ranSuccessfully && $attempts < $this->maxAttempts);

            if (!$ranSuccessfully) {
                throw new \RuntimeException(\sprintf('Projection "%s" could not be run. It\'s state is "%s". Attempted %d times.', $projectionName, $state->getValue(), $attempts));
            }
        } catch (\Prooph\EventStore\Exception\ProjectionNotFound) {
            // try running
            // the likely case is the projection has not been initialized
            $this->projector->run($keepRunning);
        }
    }

    public function configure(
        string $projectionName,
        array $readModelProjectionOptions = [],
    ): ReadModelProjector {
        $this->projectionName = $projectionName;

        if (!$this->projectionManagerForProjectionsLocator->has($this->projectionName)) {
            throw new \RuntimeException(\sprintf('ProjectionManager for "%s" not found', $this->projectionName));
        }
        $this->projectionManager = $this->projectionManagerForProjectionsLocator
            ->get($this->projectionName);

        if (!$this->projectionsLocator->has($this->projectionName)) {
            throw new \RuntimeException(\sprintf('Projection "%s" not found', $this->projectionName));
        }
        /** @var ReadModelProjection $projection */
        $projection = $this->projectionsLocator->get($this->projectionName);

        if (!$this->projectionReadModelLocator->has($this->projectionName)) {
            throw new \RuntimeException(\sprintf('ReadModel for "%s" not found', $this->projectionName));
        }
        /** @var ReadModel $readModel */
        $readModel = $this->projectionReadModelLocator->get($this->projectionName);

        $this->projector = $projection->project(
            $this->projectionManager->createReadModelProjection(
                $this->projectionName,
                $readModel,
                $readModelProjectionOptions,
            ),
        );

        return $this->projector;
    }

    public function state(): ProjectionStatus
    {
        return $this->projectionManager->fetchProjectionStatus(
            $this->projectionName,
        );
    }

    public function getAllProjectionNames(int $limit = 20): array
    {
        return $this->projectionsManager->fetchProjectionNames(null, $limit);
    }
}
