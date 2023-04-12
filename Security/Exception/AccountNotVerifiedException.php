<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Security\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class AccountNotVerifiedException extends AccountStatusException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey(): string
    {
        return 'Account is not verified.';
    }
}
