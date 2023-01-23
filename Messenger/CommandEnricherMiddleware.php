<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Xm\SymfonyBundle\DataProvider\IssuerProvider;
use Xm\SymfonyBundle\Messaging\DomainEvent;

class CommandEnricherMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly IssuerProvider $issuerProvider)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        /** @var DomainEvent $message */
        $message = $envelope->getMessage();

        if (!$message instanceof DomainEvent) {
            return $stack->next()->handle($envelope, $stack);
        }

        $message = $message->withAddedMetadata(
            'issuedBy',
            $this->issuerProvider->getIssuer(),
        );

        $newEnvelope = new Envelope(
            $message,
            array_merge(...array_values($envelope->all())),
        );

        return $stack->next()->handle($newEnvelope, $stack);
    }
}
