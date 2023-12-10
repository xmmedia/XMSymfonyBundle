<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Command;

use Prooph\EventStore\Projection\ProjectionManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:projection:delete',
    description: 'Delete a projection.',
)]
class ProjectionDeleteCommand extends Command
{
    protected const ARGUMENT_PROJECTION_NAME = 'projection-name';
    protected const OPTION_WITH_EVENTS = 'with-emitted-events';

    public function __construct(private readonly ProjectionManager $projectionsManager)
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
            ->addOption(static::OPTION_WITH_EVENTS, 'w', InputOption::VALUE_NONE, 'Delete with emitted events');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectionName = $input->getArgument(static::ARGUMENT_PROJECTION_NAME);
        $withEvents = $input->getOption(self::OPTION_WITH_EVENTS);

        $message = \sprintf(
            '<action>Deleting projection <highlight>%s</highlight>%s</action>',
            $projectionName,
            $withEvents ? ' with emitted events' : ' without emitted events',
        );
        $output->writeln($message);

        $this->projectionsManager->deleteProjection($projectionName, $withEvents);

        return 0;
    }
}
