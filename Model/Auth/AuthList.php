<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\Auth;

interface AuthList
{
    public function save(Auth $auth): void;

    public function get(AuthId $authId): ?Auth;
}
