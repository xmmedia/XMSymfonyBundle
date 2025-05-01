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
    private string $projectionName;
    private ProjectionManager $projectionManager;
    private ReadModelProjector $projector;

    public function __construct(
        private readonly ProjectionManager $projectionsManager,
        private readonly ContainerInterface $projectionManagerForProjectionsLocator,
        private readonly ContainerInterface $projectionsLocator,
        private readonly ContainerInterface $projectionReadModelLocator,
        private readonly ?LoggerInterface $logger = null,
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
                    // 1/2 second
                    usleep(500000);
                }

                $state = $this->state();
                ++$attempts;

                if ($state->is(ProjectionStatus::IDLE())) {
                    try {
                        $this->projector->run($keepRunning);
                        $ranSuccessfully = true;
                    } catch (\Prooph\EventStore\Exception\RuntimeException $e) {
                        // if the projection is already running, we can ignore this exception
                        if ($attempts >= 50 || $e->getMessage() !== 'Another projection process is already running') {
                            throw $e;
                        }
                    }
                } else if ($attempts > 50) {
                    throw new \RuntimeException(sprintf('Projection "%s" is not idle. It\'s state is "%s". Attempted %d times.', $projectionName, $state->getValue(), $attempts));
                }

                if ($attempts > 1 && 0 === $attempts % 5 && $this->logger) {
                    $this->logger->warning(
                        sprintf(
                            'Projection "%s" could not be run. When checked, it\'s state was "%s". Attempted to run %d times.',
                            $projectionName,
                            $state->getValue(),
                            $attempts,
                        ),
                    );
                }
            } while (!$ranSuccessfully && !$state->is(ProjectionStatus::IDLE()) && $attempts < 50);

        } catch (\Prooph\EventStore\Exception\ProjectionNotFound $e) {
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
            throw new \RuntimeException(sprintf('ProjectionManager for "%s" not found', $this->projectionName));
        }
        $this->projectionManager = $this->projectionManagerForProjectionsLocator
            ->get($this->projectionName);

        if (!$this->projectionsLocator->has($this->projectionName)) {
            throw new \RuntimeException(sprintf('Projection "%s" not found', $this->projectionName));
        }
        /** @var ReadModelProjection $projection */
        $projection = $this->projectionsLocator->get($this->projectionName);

        if (!$this->projectionReadModelLocator->has($this->projectionName)) {
            throw new \RuntimeException(sprintf('ReadModel for "%s" not found', $this->projectionName));
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
