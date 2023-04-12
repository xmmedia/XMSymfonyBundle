<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Command;

use Prooph\EventStore\Pdo\Projection\PdoEventStoreProjector;
use Prooph\EventStore\Projection\ReadModelProjector;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Xm\SymfonyBundle\Infrastructure\Service\ProjectionRunner;

#[AsCommand(
    name: 'app:projection:run',
    description: 'Runs a projection.',
)]
final class ProjectionRunCommand extends Command
{
    protected const ARGUMENT_PROJECTION_NAME = 'projection-name';
    protected const OPTION_RUN_ALL = 'run-all';
    protected const OPTION_RUN_ONCE = 'run-once';
    protected const OPTION_SLEEP = 'sleep';

    private string $projectionName;
    private ReadModelProjector $projector;
    private SymfonyStyle $io;

    public function __construct(private readonly ProjectionRunner $projectionRunner)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                static::ARGUMENT_PROJECTION_NAME,
                InputArgument::OPTIONAL,
                'The name of the Projection',
            )
            ->addOption(
                static::OPTION_RUN_ALL,
                null,
                InputOption::VALUE_NONE,
                'Run all projections once',
            )
            ->addOption(
                static::OPTION_RUN_ONCE,
                'o',
                InputOption::VALUE_NONE,
                'Loop the projection only once, then exit. Not supported when running all',
            )
            ->addOption(
                static::OPTION_SLEEP,
                's',
                InputOption::VALUE_REQUIRED,
                'The sleep time of the projector in microseconds',
                1000000, // 1 second
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Running Projection(s)');
        $this->io->text((new \DateTimeImmutable())->format('Y-m-d H:i:s'));

        $runAll = $input->getOption(static::OPTION_RUN_ALL);
        if (!$runAll) {
            $this->projectionName = $input->getArgument(
                static::ARGUMENT_PROJECTION_NAME,
            );
        }
        $keepRunning = !$input->getOption(static::OPTION_RUN_ONCE);
        $sleep = (int) $input->getOption(static::OPTION_SLEEP);

        if (!isset($this->projectionName) && !$runAll) {
            throw new RuntimeException('A projection name or --all for run all projections are required.');
        }

        if ($runAll) {
            $this->runAllProjections();
        } else {
            $this->runProjection($keepRunning, $sleep);
        }

        return 0;
    }

    private function runProjection(bool $keepRunning, int $sleep): void
    {
        $this->projector = $this->projectionRunner->configure(
            $this->projectionName,
            [
                PdoEventStoreProjector::OPTION_SLEEP          => $sleep,
                PdoEventStoreProjector::OPTION_PCNTL_DISPATCH => true,
            ],
        );

        $this->io->text(
            sprintf('Initialized projection "%s"', $this->projectionName),
        );

        try {
            $state = $this->projectionRunner->state()->getValue();
        } catch (\Prooph\EventStore\Exception\RuntimeException $e) {
            $state = 'unknown';
        }
        $this->io->text(sprintf('Current status: %s', $state));

        $this->io->text(
            sprintf('Starting projection "%s"', $this->projectionName),
        );
        $this->io->text(
            sprintf(
                'Keep running %s',
                true === $keepRunning ? 'enabled' : 'disabled',
            ),
        );

        $this->setupPcntl();

        $this->projector->run($keepRunning);

        $this->io->text((new \DateTimeImmutable())->format('Y-m-d H:i:s'));
        $this->io->success(
            sprintf('Projection %s completed.', $this->projectionName),
        );
    }

    private function runAllProjections(): void
    {
        $projections = $this->projectionRunner->getAllProjectionNames();
        foreach ($projections as $projectionName) {
            $this->projectionName = $projectionName;

            $this->runProjection(false, 1000000);
        }
    }

    private function setupPcntl(): void
    {
        pcntl_signal(\SIGTERM, [$this, 'signalHandler']);
        pcntl_signal(\SIGHUP, [$this, 'signalHandler']);
        pcntl_signal(\SIGINT, [$this, 'signalHandler']);
        pcntl_signal(\SIGQUIT, [$this, 'signalHandler']);
    }

    public function signalHandler(): void
    {
        $this->io->success(
            sprintf('Projection %s stopped.', $this->projectionName),
        );
        $this->projector->stop();
    }
}
