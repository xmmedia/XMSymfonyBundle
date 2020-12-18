<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Mockery;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\User\UserInterface;

trait VoterTestTrait
{
    private function getLoggedInToken(): TokenInterface
    {
        $token = Mockery::mock(TokenInterface::class);
        $token->shouldReceive('getUser')
            ->andReturn(Mockery::mock(UserInterface::class));

        return $token;
    }

    /**
     * @param bool[] ...$results The results of the decide() method calls.
     */
    private function getAccessDecisionManager(...$results): AccessDecisionManager
    {
        $decisionManager = Mockery::mock(AccessDecisionManager::class);

        foreach ($results as $result) {
            $decisionManager->shouldReceive('decide')
                ->once()
                ->andReturn($result);
        }

        return $decisionManager;
    }
}
