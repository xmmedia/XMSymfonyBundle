<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Exception;

use Xm\SymfonyBundle\Model\User\UserId;

final class UserAlreadyVerified extends \RuntimeException
{
    public static function triedToVerify(UserId $userId): self
    {
        return new self(sprintf(
            'Tried to verify the user "%s" that\'s already verified.',
            $userId
        ));
    }

    public static function triedToSendVerification(UserId $userId): self
    {
        return new self(sprintf(
            'Tried to send verification to user "%s" but they\'re already verified.',
            $userId
        ));
    }
}
