<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Projection\User;

use Xm\SymfonyBundle\Util\Filters;

class UserFilters extends Filters
{
    public const ACTIVE = 'active';
    public const EMAIL = 'email';
    public const EMAIL_EXACT = 'emailExact';

    protected function parseFilters(array $filters): array
    {
        if (\array_key_exists(self::ACTIVE, $filters)) {
            $filters[self::ACTIVE] = $this->isTrue($filters[self::ACTIVE]);
        }

        return $filters;
    }
}
