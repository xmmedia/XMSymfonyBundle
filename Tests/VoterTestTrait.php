<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

trait VoterTestTrait
{
    private function getLoggedInToken(): TokenInterface
    {
        $token = \Mockery::mock(TokenInterface::class);
        $token->shouldReceive('getUser')
            ->andReturn(\Mockery::mock(UserInterface::class));

        return $token;
    }
}
