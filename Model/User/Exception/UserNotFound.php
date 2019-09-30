<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Exception;

use Xm\SymfonyBundle\Model\User\UserId;

final class UserNotFound extends \InvalidArgumentException
{
    public static function withUserId(UserId $userId): self
    {
        return new self(sprintf('User with id "%s" cannot be found.', $userId));
    }
}
