<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Messenger;

use Doctrine\DBAL\Connection;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Xm\SymfonyBundle\Messaging\Command;

class CommandRecorderMiddleware implements MiddlewareInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (null !== $envelope->last(ReceivedStamp::class)) {
            return $stack->next()->handle($envelope, $stack);
        }
        
        $message = $envelope->getMessage();

        if ($message instanceof Command) {
            $this->record($message);
        }

        return $stack->next()->handle($envelope, $stack);
    }

    public function record(Command $command): void
    {
        $this->connection->insert('command_log', [
            'command_id' => $command->uuid()->toString(),
            'command'    => \get_class($command),
            'payload'    => $command->payload(),
            'metadata'   => $command->metadata(),
            'sent_at'    => $command->createdAt(),
        ], [
            'payload'  => 'json',
            'metadata' => 'json',
            'sent_at'  => 'datetime_microseconds',
        ]);
    }
}
