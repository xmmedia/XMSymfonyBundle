<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Exception;

use Xm\SymfonyBundle\Model\User\Token;

final class TokenHasExpired extends \InvalidArgumentException
{
    public static function before(Token $token, string $ttl): self
    {
        return new self(
            sprintf('The token %s has expired (ttl: %s).', $token, $ttl)
        );
    }
}
