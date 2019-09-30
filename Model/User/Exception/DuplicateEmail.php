<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Exception;

use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\UserId;

final class DuplicateEmail extends \InvalidArgumentException
{
    public static function withEmail(Email $email, UserId $userId): self
    {
        return new self(
            sprintf(
                'The email address "%s" is already used by user "%s".',
                $email,
                $userId
            )
        );
    }
}
