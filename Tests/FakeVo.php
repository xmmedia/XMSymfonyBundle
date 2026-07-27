<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Xm\SymfonyBundle\Model\ValueObject;

class FakeVo implements ValueObject
{
    public static function create(): self
    {
        return new self();
    }

    public function sameValueAs(self|ValueObject $other): bool
    {
        return false;
    }
}
