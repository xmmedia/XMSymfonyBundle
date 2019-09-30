<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Handler;

use Xm\SymfonyBundle\Model\User\Command\AdminAddUser;
use Xm\SymfonyBundle\Model\User\Service\ChecksUniqueUsersEmail;
use Xm\SymfonyBundle\Model\User\User;
use Xm\SymfonyBundle\Model\User\UserList;

class AdminAddUserHandler
{
    /** @var UserList */
    private $userRepo;

    /** @var ChecksUniqueUsersEmail */
    private $checksUniqueUsersEmail;

    public function __construct(
        UserList $userRepo,
        ChecksUniqueUsersEmail $checksUniqueUsersEmail
    ) {
        $this->userRepo = $userRepo;
        $this->checksUniqueUsersEmail = $checksUniqueUsersEmail;
    }

    public function __invoke(AdminAddUser $command): void
    {
        $user = User::addByAdmin(
            $command->userId(),
            $command->email(),
            $command->encodedPassword(),
            $command->role(),
            $command->active(),
            $command->firstName(),
            $command->lastName(),
            $command->sendInvite(),
            $this->checksUniqueUsersEmail
        );

        $this->userRepo->save($user);
    }
}
