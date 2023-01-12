<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventStore;

use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Metadata\MetadataEnricher;
use Xm\SymfonyBundle\DataProvider\CausationProvider;

class MetadataCausationEnricher implements MetadataEnricher
{
    public function __construct(private readonly CausationProvider $causationProvider)
    {
    }

    public function enrich(Message $message): Message
    {
        if (null === $this->causationProvider->retrieveCausationId()) {
            return $message;
        }

        return $message->withAddedMetadata(
            'causationId',
            $this->causationProvider->retrieveCausationId()->toString(),
        );
    }
}
