<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventStore;

use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Metadata\MetadataEnricher;
use Xm\SymfonyBundle\DataProvider\IssuerProvider;

class MetadataIssuedByEnricher implements MetadataEnricher
{
    /** @var IssuerProvider */
    private $issuerProvider;

    public function __construct(IssuerProvider $issuerProvider)
    {
        $this->issuerProvider = $issuerProvider;
    }

    public function enrich(Message $message): Message
    {
        return $message->withAddedMetadata(
            'issuedBy',
            $this->issuerProvider->getIssuer()
        );
    }
}
