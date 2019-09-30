<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Repository;

use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRepository;
use Xm\SymfonyBundle\Model\Auth\Auth;
use Xm\SymfonyBundle\Model\Auth\AuthId;
use Xm\SymfonyBundle\Model\Auth\AuthList;

final class AuthRepository extends AggregateRepository implements AuthList
{
    public function save(Auth $auth): void
    {
        $this->saveAggregateRoot($auth);
    }

    public function get(AuthId $authId): ?Auth
    {
        return $this->getAggregateRoot($authId->toString());
    }
}
