<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:projection:rebuild',
    description: 'Rebuilds a projection.',
)]
final class ProjectionRebuildCommand extends Command
{
    protected const ARGUMENT_PROJECTION_NAME = 'projection-name';

    protected function configure(): void
    {
        $this
            ->addArgument(
                static::ARGUMENT_PROJECTION_NAME,
                InputArgument::REQUIRED,
                'The name of the Projection, with or without "_projection" suffix',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectionName = $input->getArgument(static::ARGUMENT_PROJECTION_NAME);
        // append _projection if not present
        if (!str_ends_with($projectionName, '_projection')) {
            $projectionName .= '_projection';
        }

        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Rebuilding Projection "%s"', $projectionName));

        $this->getApplication()
            ->find('app:projection:delete')
            ->run(
                new ArrayInput([
                    // for some reason the prooph command for delete requires the "command"
                    'command'               => 'event-store:projection:delete',
                    'projection-name'       => $projectionName,
                    '--with-emitted-events' => true,
                ]),
                $output,
            );

        // run to perform the delete
        $this->getApplication()
            ->find('app:projection:run')
            ->run(
                new ArrayInput([
                    'projection-name' => $projectionName,
                    '-o'              => true,
                ]),
                $output,
            );

        // run again to rebuild
        return $this->getApplication()
            ->find('app:projection:run')
            ->run(
                new ArrayInput([
                    'projection-name' => $projectionName,
                    '-o'              => true,
                ]),
                $output,
            );
    }
}
