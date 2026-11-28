<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Error;

class TooManyRequestsError extends CodedUserError
{
    #[\Override]
    public function errorCode(): string
    {
        return 'TOO_MANY_REQUESTS';
    }
}
