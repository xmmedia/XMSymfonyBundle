<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User;

use Xm\SymfonyBundle\Model\UuidId;
use Xm\SymfonyBundle\Model\UuidInterface;
use Xm\SymfonyBundle\Model\ValueObject;

class UserId implements ValueObject, UuidInterface
{
    use UuidId;
}
