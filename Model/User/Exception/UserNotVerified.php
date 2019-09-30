<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Exception;

use Xm\SymfonyBundle\Model\User\UserId;

final class UserNotVerified extends \RuntimeException
{
    public static function triedToLogin(UserId $userId): self
    {
        return new self(
            sprintf(
                'User "%s" tried to login but they\'re account is not verified.',
                $userId
            )
        );
    }
}
