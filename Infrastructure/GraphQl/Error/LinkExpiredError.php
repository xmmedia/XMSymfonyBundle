<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Error;

class LinkExpiredError extends CodedUserError
{
    #[\Override]
    public function errorCode(): string
    {
        return 'LINK_EXPIRED';
    }
}
