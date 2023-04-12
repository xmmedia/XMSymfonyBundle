<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Command;

use Prooph\EventStore\EventStore;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'event-store:event-stream:create',
    description: 'Create event_stream.',
)]
final class CreateEventStreamCommand extends Command
{
    public function __construct(private readonly EventStore $eventStore)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setHelp('This command creates the event_stream')
            ->addArgument(
                'stream_name',
                InputArgument::REQUIRED,
                'The name of the event stream. This will also be used in the table name. Don\'t include "_event_stream".',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $streamName = $input->getArgument('stream_name');

        $this->eventStore->create(
            new Stream(
                new StreamName($streamName),
                new \ArrayIterator([]),
            ),
        );

        $output->writeln('<info>Event stream "'.$streamName.'" was created successfully.</info>');

        return 0;
    }
}
