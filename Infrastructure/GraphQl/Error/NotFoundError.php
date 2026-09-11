<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Error;

class NotFoundError extends CodedUserError
{
    public function errorCode(): string
    {
        return 'NOT_FOUND';
    }
}
