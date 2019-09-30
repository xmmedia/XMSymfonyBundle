<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

interface ValueObject
{
    public function sameValueAs(self $other): bool;
}
