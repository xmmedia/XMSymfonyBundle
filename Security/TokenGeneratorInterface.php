<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Security;

use Xm\SymfonyBundle\Model\User\Token;

interface TokenGeneratorInterface
{
    public function __invoke(): Token;
}
