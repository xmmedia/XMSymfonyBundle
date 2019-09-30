<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Handler;

use Xm\SymfonyBundle\Model\User\Command\AdminChangePassword;
use Xm\SymfonyBundle\Model\User\Exception\UserNotFound;
use Xm\SymfonyBundle\Model\User\UserList;

class AdminChangePasswordHandler
{
    /** @var UserList */
    private $userRepo;

    public function __construct(UserList $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function __invoke(AdminChangePassword $command): void
    {
        $user = $this->userRepo->get($command->userId());

        if (!$user) {
            throw UserNotFound::withUserId($command->userId());
        }

        $user->changePasswordByAdmin($command->encodedPassword());

        $this->userRepo->save($user);
    }
}
