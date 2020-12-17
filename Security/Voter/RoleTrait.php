<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @property \Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface $decisionManager
 */
trait RoleTrait
{
    protected function hasRole(TokenInterface $token, string $role): bool
    {
        return $this->decisionManager->decide($token, [$role]);
    }

    protected function isAdmin(TokenInterface $token): bool
    {
        return $this->hasRole($token, 'ROLE_ADMIN');
    }
}
