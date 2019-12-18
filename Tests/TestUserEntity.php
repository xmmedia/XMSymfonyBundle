<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Symfony\Component\Security\Core\User\UserInterface;

class TestUserEntity implements UserInterface
{
    private $userId;

    public function userId()
    {
        return $this->userId;
    }

    public function getRoles()
    {
        return [];
    }

    public function getPassword()
    {
        return '';
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return null;
    }

    public function eraseCredentials()
    {
    }
}
