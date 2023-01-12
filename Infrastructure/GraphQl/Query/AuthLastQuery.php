<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Query;

use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class AuthLastQuery implements QueryInterface
{
    public function __construct(
        private readonly AuthenticationUtils $authenticationUtils,
        private readonly TranslatorInterface $trans,
    ) {
    }

    public function __invoke(): array
    {
        $errorMsg = null;
        if ($error = $this->authenticationUtils->getLastAuthenticationError()) {
            $errorMsg = $this->trans->trans(
                $error->getMessageKey(),
                $error->getMessageData(),
                'security',
            );
        }

        return [
            'email' => $this->authenticationUtils->getLastUsername(),
            'error' => $errorMsg,
        ];
    }
}
