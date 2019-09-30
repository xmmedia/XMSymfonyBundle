<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\Auth\Handler;

use Xm\SymfonyBundle\Model\Auth\Auth;
use Xm\SymfonyBundle\Model\Auth\AuthList;
use Xm\SymfonyBundle\Model\Auth\Command\UserLoginFailed;

class UserLoginFailedHandler
{
    /** @var AuthList */
    private $authRepo;

    public function __construct(AuthList $authRepo)
    {
        $this->authRepo = $authRepo;
    }

    public function __invoke(UserLoginFailed $command): void
    {
        $auth = Auth::failure(
            $command->authId(),
            $command->email(),
            $command->userAgent(),
            $command->ipAddress(),
            $command->exceptionMessage()
        );

        $this->authRepo->save($auth);
    }
}
