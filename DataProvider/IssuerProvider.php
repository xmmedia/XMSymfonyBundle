<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataProvider;

use Symfony\Bundle\SecurityBundle\Security;

class IssuerProvider
{
    public function __construct(private readonly Security $security)
    {
    }

    public function getIssuer(): string|int|null
    {
        if (null === $this->security->getToken()) {
            if ('cli' === php_sapi_name()) {
                return 'cli';
            } else {
                return 'anonymous';
            }
        }

        /** @var \Symfony\Component\Security\Core\User\UserInterface $user */
        $user = $this->security->getUser();

        if (!$user) {
            return 'anonymous';
        }

        if (is_int($user->userId())) {
            return $user->userId();
        }

        return (string) $user->userId();
    }
}
