<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Xm\SymfonyBundle\Util\PasswordStrengthInterface;

class PasswordStrengthFake implements PasswordStrengthInterface
{
    public function __construct(private readonly int $score = 4)
    {
    }

    public function __invoke(string $password, array $userInputs = []): array
    {
        return [
            'score'    => $this->score,
            'password' => $password,
        ];
    }
}
