<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Repository;

use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRepository;
use Xm\SymfonyBundle\Model\User\User;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Model\User\UserList;

final class UserRepository extends AggregateRepository implements UserList
{
    public function save(User $user): void
    {
        $this->saveAggregateRoot($user);
    }

    public function get(UserId $userId): ?User
    {
        return $this->getAggregateRoot($userId->toString());
    }
}
