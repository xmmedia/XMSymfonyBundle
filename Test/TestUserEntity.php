<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Test;

use Symfony\Component\Security\Core\User\UserInterface;

class TestUserEntity implements UserInterface
{
    private $userId;

    public function userId()
    {
        return $this->userId;
    }

    #[\Override]
    public function getRoles(): array
    {
        return [];
    }

    public function getPassword(): string
    {
        return '';
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    #[\Override]
    public function getUserIdentifier(): string
    {
        return '';
    }

    public function eraseCredentials(): void
    {
    }
}
