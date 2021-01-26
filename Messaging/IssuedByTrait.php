<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Messaging;

trait IssuedByTrait
{
    public function withIssuedBy(string $issuer): DomainMessage
    {
        return self::withAddedMetadata('issuedBy', $issuer);
    }
}
