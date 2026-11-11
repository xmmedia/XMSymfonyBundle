<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Command;

use Prooph\EventStore\Pdo\Projection\PdoEventStoreProjector;
use Prooph\EventStore\Projection\Projector;
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
    use ProjectionNameTrait;

    protected const string ARGUMENT_PROJECTION_NAME = 'projection-name';
    protected const string OPTION_RUN_ALL = 'run-all';
    protected const string OPTION_RUN_ONCE = 'run-once';
    protected const string OPTION_SLEEP = 'sleep';
    protected const string OPTION_LOAD_COUNT = 'load-count';

    private string $projectionName;
    /** The projector that's running, so a signal can stop it. */
    private ?ReadModelProjector $activeProjector = null;
    private bool $stopRequested = false;
    private SymfonyStyle $io;

    public function __construct(private readonly ProjectionRunner $projectionRunner)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                self::ARGUMENT_PROJECTION_NAME,
                InputArgument::OPTIONAL,
                'The name of the Projection',
            )
            ->addOption(
                self::OPTION_RUN_ALL,
                null,
                InputOption::VALUE_NONE,
                'Run all projections once',
            )
            ->addOption(
                self::OPTION_RUN_ONCE,
                'o',
                InputOption::VALUE_NONE,
                'Loop the projection only once, then exit. Not supported when running all',
            )
            ->addOption(
                self::OPTION_SLEEP,
                's',
                InputOption::VALUE_REQUIRED,
                'The sleep time of the projector in microseconds',
                1000000, // 1 second
            )
            ->addOption(
                self::OPTION_LOAD_COUNT,
                null,
                InputOption::VALUE_REQUIRED,
                'The number of events to load and process at once. Default is unlimited.',
                null,
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        // the command can be run more than once in a process
        $this->stopRequested = false;
        $this->io->title('Running Projection(s)');
        $this->io->text((new \DateTimeImmutable())->format('Y-m-d H:i:s'));

        $runAll = $input->getOption(self::OPTION_RUN_ALL);
        $projectionName = $input->getArgument(self::ARGUMENT_PROJECTION_NAME);
        if (!$runAll && null !== $projectionName) {
            $this->projectionName = self::projectionName($projectionName);
        }
        $keepRunning = !$input->getOption(self::OPTION_RUN_ONCE);
        $sleep = (int) $input->getOption(self::OPTION_SLEEP);
        $loadCount = $input->getOption(self::OPTION_LOAD_COUNT);
        if (null !== $loadCount) {
            $loadCount = (int) $loadCount;
        }

        if (!isset($this->projectionName) && !$runAll) {
            throw new RuntimeException('A projection name or --run-all to run all projections is required.');
        }

        if ($runAll) {
            $this->runAllProjections();
        } else {
            $this->runProjection($keepRunning, $sleep, $loadCount);
        }

        return Command::SUCCESS;
    }

    private function runProjection(bool $keepRunning, int $sleep, ?int $loadCount): void
    {
        $projector = $this->projectionRunner->configure(
            $this->projectionName,
            [
                Projector::OPTION_SLEEP                   => $sleep,
                Projector::OPTION_PCNTL_DISPATCH          => true,
                PdoEventStoreProjector::OPTION_LOAD_COUNT => $loadCount,
            ],
        );

        $this->io->text(
            \sprintf('Initialized projection "%s"', $this->projectionName),
        );

        try {
            $state = $this->projectionRunner->state()->getValue();
        } catch (\Prooph\EventStore\Exception\RuntimeException) {
            $state = 'unknown';
        }
        $this->io->text(\sprintf('Current status: %s', $state));

        $this->io->text(
            \sprintf('Starting projection "%s"', $this->projectionName),
        );
        $this->io->text(
            \sprintf(
                'Keep running %s',
                $keepRunning ? 'enabled' : 'disabled',
            ),
        );
        $this->io->text(
            \sprintf('Running with %s', null === $loadCount ? 'unlimited events' : $loadCount.' events'),
        );

        $previousSignalHandling = $this->setupPcntl();
        $this->activeProjector = $projector;

        try {
            $projector->run($keepRunning);
        } finally {
            $this->activeProjector = null;
            $this->restorePcntl($previousSignalHandling);
        }

        $this->io->text((new \DateTimeImmutable())->format('Y-m-d H:i:s'));
        if (!$this->stopRequested) {
            $this->io->success(
                \sprintf('Projection %s completed.', $this->projectionName),
            );
        }
    }

    private function runAllProjections(): void
    {
        $projections = $this->projectionRunner->getAllProjectionNames();
        foreach ($projections as $projectionName) {
            // a signal stopped the previous projection
            if ($this->stopRequested) {
                break;
            }

            $this->projectionName = $projectionName;

            $this->runProjection(false, 1000000, null);
        }
    }

    /**
     * Signals are only handled where the projector calls pcntl_signal_dispatch():
     * between events & before it checks its status, after it's loaded its positions.
     * Handled asynchronously (which the console Application turns on), the projector
     * could be stopped part way through an event, or before it's loaded its positions,
     * saving them as empty so the next run handles every event again.
     * pcntl is always available: the projector requires it for OPTION_PCNTL_DISPATCH.
     *
     * @return array{async: bool, handlers: array<int, callable|int>} the signal handling to restore
     */
    private function setupPcntl(): array
    {
        $previous = [
            'async'    => pcntl_async_signals(false),
            'handlers' => [],
        ];

        foreach ([\SIGTERM, \SIGHUP, \SIGINT, \SIGQUIT] as $signal) {
            $previous['handlers'][$signal] = pcntl_signal_get_handler($signal);
            pcntl_signal($signal, $this->signalHandler(...));
        }

        return $previous;
    }

    /**
     * @param array{async: bool, handlers: array<int, callable|int>} $previous
     */
    private function restorePcntl(array $previous): void
    {
        // handle a signal that came after the projector's last dispatch,
        // so it stops the rest of --run-all instead of going to the previous handler
        pcntl_signal_dispatch();

        foreach ($previous['handlers'] as $signal => $handler) {
            pcntl_signal($signal, $handler);
        }

        pcntl_async_signals($previous['async']);
    }

    public function signalHandler(): void
    {
        // only the first signal stops the projector
        $alreadyRequested = $this->stopRequested;
        $this->stopRequested = true;

        // nothing to stop once the projector's finished
        if ($alreadyRequested || null === $this->activeProjector) {
            return;
        }

        $this->io->success(
            \sprintf('Projection %s stopped.', $this->projectionName),
        );
        $this->activeProjector->stop();
    }
}
