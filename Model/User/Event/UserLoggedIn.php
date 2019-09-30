<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Event;

use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\User\UserId;

class UserLoggedIn extends AggregateChanged
{
    public static function now(UserId $userId): self
    {
        $event = self::occur($userId->toString());

        return $event;
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->aggregateId());
    }
}
