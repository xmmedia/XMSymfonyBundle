<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

interface Entity
{
    public function sameIdentityAs(self $other): bool;
}
