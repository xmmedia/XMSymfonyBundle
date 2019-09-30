<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User;

interface UserList
{
    public function save(User $user): void;

    public function get(UserId $userId): ?User;
}
