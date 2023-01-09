<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Mockery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

trait CanCreateSecurityTrait
{
    /**
     * $user: false = no token storage within container, null = no user.
     *
     * @param UserInterface|bool|null $user
     *
     * @return Security|Mockery\MockInterface
     */
    private function createSecurity($user): Security
    {
        $tokenStorage = \Mockery::mock(TokenStorageInterface::class);

        if (false !== $user) {
            $token = \Mockery::mock(TokenInterface::class);
            $token->shouldReceive('getUser')
                ->andReturn($user);

            $tokenStorage->shouldReceive('getToken')
                ->andReturn($token);
        }

        $container = $this->createContainer('security.token_storage', $tokenStorage);

        return new Security($container);
    }

    /**
     * @return ContainerInterface|Mockery\MockInterface
     */
    private function createContainer(
        string $serviceId,
        object $serviceObject
    ): ContainerInterface {
        $container = \Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with($serviceId)
            ->andReturn($serviceObject);

        return $container;
    }
}
