<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\Auth\Handler;

use Xm\SymfonyBundle\Model\Auth\Auth;
use Xm\SymfonyBundle\Model\Auth\AuthList;
use Xm\SymfonyBundle\Model\Auth\Command\UserLoggedInSuccessfully;

class UserLoggedInSuccessfullyHandler
{
    /** @var AuthList */
    private $authRepo;

    public function __construct(AuthList $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    public function __invoke(UserLoggedInSuccessfully $command): void
    {
        $auth = Auth::success(
            $command->authId(),
            $command->userId(),
            $command->email(),
            $command->userAgent(),
            $command->ipAddress()
        );

        $this->authRepo->save($auth);
    }
}
