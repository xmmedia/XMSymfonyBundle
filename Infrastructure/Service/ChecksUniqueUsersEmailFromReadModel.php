<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Service;

use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\Service\ChecksUniqueUsersEmail;
use Xm\SymfonyBundle\Model\User\UserIdInterface;
use Xm\SymfonyBundle\Projection\User\UserFinder;

class ChecksUniqueUsersEmailFromReadModel implements ChecksUniqueUsersEmail
{
    /** @var UserFinder */
    private $userFinder;

    public function __construct(UserFinder $userFinder)
    {
        $this->userFinder = $userFinder;
    }

    public function __invoke(Email $email): ?UserIdInterface
    {
        if ($user = $this->userFinder->findOneByEmail($email)) {
            return $user->userId();
        }

        return null;
    }
}
