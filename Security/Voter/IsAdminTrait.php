<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Security\Voter;

use JetBrains\PhpStorm\Deprecated;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @property \Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface $decisionManager
 */
#[Deprecated]
trait IsAdminTrait
{
    /**
     * True when the user has an admin role.
     */
    protected function isAdmin(TokenInterface $token): bool
    {
        trigger_deprecation(
            'xm/symfony-bundle',
            '2.0.9',
            'The "%s" trait is deprecated and will be removed in 3.0.0.',
            IsAdminTrait::class,
        );

        return $this->decisionManager->decide($token, ['ROLE_ADMIN']);
    }
}
