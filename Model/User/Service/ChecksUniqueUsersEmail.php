<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Service;

use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\UserIdInterface;

interface ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserIdInterface;
}
