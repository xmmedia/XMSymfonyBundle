<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Xm\SymfonyBundle\DataProvider\CausationProvider;
use Xm\SymfonyBundle\Messaging\DomainMessage;

class CausationRecorderMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly CausationProvider $causationProvider)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if (!$envelope->getMessage() instanceof DomainMessage) {
            return $stack->next()->handle($envelope, $stack);
        }

        $this->causationProvider->storeCausationId(
            $envelope->getMessage()->uuid(),
        );

        return $stack->next()->handle($envelope, $stack);
    }
}
