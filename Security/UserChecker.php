<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Xm\SymfonyBundle\Entity\User;
use Xm\SymfonyBundle\Security\Exception\AccountInactiveException;
use Xm\SymfonyBundle\Security\Exception\AccountNotVerifiedException;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }
    }

    /**
     * Exceptions/messages generated here can be displayed to the user
     * because they've entered the correct password.
     */
    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->verified()) {
            $ex = new AccountNotVerifiedException('User account has not been verified.');
            $ex->setUser($user);
            throw $ex;
        }

        if (!$user->active()) {
            $ex = new AccountInactiveException('User account is not active.');
            $ex->setUser($user);
            throw $ex;
        }
    }
}
