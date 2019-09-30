<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Security;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Xm\SymfonyBundle\Entity\User;
use Xm\SymfonyBundle\Model\User\Role;

class PasswordEncoder
{
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function __invoke(Role $role, string $password): string
    {
        return $this->passwordEncoder->encodePassword(
            $this->getUserForRole($role),
            $password
        );
    }

    private function getUserForRole(Role $role): User
    {
        $user = new User();

        $reflection = new \ReflectionClass(User::class);
        $property = $reflection->getProperty('roles');
        $property->setAccessible(true);
        $property->setValue($user, [$role->getValue()]);

        return $user;
    }
}
