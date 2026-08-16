<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

interface UuidInterface extends \Stringable
{
    public function toString(): string;
}
