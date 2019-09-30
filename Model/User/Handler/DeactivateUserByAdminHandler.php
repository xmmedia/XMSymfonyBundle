<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Handler;

use Xm\SymfonyBundle\Model\User\Command\DeactivateUserByAdmin;
use Xm\SymfonyBundle\Model\User\Exception\UserNotFound;
use Xm\SymfonyBundle\Model\User\UserList;

class DeactivateUserByAdminHandler
{
    /** @var UserList */
    private $userRepo;

    public function __construct(UserList $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function __invoke(DeactivateUserByAdmin $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $user->deactivateByAdmin();

        $this->userRepo->save($user);
    }
}
