<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataProvider;

use Symfony\Bundle\SecurityBundle\Security;

class IssuerProvider
{
    public function __construct(private readonly Security $security)
    {
    }

    public function getIssuer(): string
    {
        if (null === $token = $this->security->getToken()) {
            return 'cli';
        }

        /** @var \Symfony\Component\Security\Core\User\UserInterface $user */
        $user = $this->security->getUser();

        if (!$user) {
            return 'anonymous';
        }

        return $user->userId()->toString();
    }
}
