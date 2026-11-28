<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Test;

use Xm\SymfonyBundle\Model\Entity;

class FakeAr implements Entity
{
    public static function create(): self
    {
        return new self();
    }

    #[\Override]
    public function sameIdentityAs(Entity $other): bool
    {
        return false;
    }
}
