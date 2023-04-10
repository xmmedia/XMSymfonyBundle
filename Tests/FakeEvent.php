<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Ramsey\Uuid\Uuid;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class FakeEvent extends AggregateChanged
{
    public static function performed(): self
    {
        return self::occur(Uuid::uuid4()->toString());
    }
}
