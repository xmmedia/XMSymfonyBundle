<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Xm\SymfonyBundle\DataProvider\CausationProvider;

class CausationRecorderMiddleware implements MiddlewareInterface
{
    /** @var CausationProvider */
    private $causationProvider;

    public function __construct(CausationProvider $causationProvider)
    {
        $this->causationProvider = $causationProvider;
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $this->causationProvider->storeCausationId(
            $envelope->getMessage()->uuid(),
        );

        return $stack->next()->handle($envelope, $stack);
    }
}
