<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventStore;

use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Metadata\MetadataEnricher;
use Xm\SymfonyBundle\DataProvider\IssuerProvider;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;

class MetadataUserAgentEnricher implements MetadataEnricher
{
    public function __construct(private readonly RequestInfoProvider $requestInfoProvider)
    {
    }

    public function enrich(Message $message): Message
    {
        return $message->withAddedMetadata(
            'userAgent',
            $this->requestInfoProvider->userAgent(),
        );
    }
}
