<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventStore;

use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Metadata\MetadataEnricher;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;

class MetadataIpAddressEnricher implements MetadataEnricher
{
    public function __construct(private readonly RequestInfoProvider $requestInfoProvider)
    {
    }

    public function enrich(Message $message): Message
    {
        $ipAddress = $this->requestInfoProvider->ipAddress();
        if (null === $ipAddress) {
            return $message;
        }

        return $message->withAddedMetadata('ipAddress', $ipAddress);
    }
}
