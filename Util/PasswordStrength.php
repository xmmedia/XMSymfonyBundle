<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Util;

use ZxcvbnPhp\Zxcvbn;

class PasswordStrength implements PasswordStrengthInterface
{
    public function __invoke(string $password, array $userInputs = []): array
    {
        return (new Zxcvbn())->passwordStrength($password, array_values($userInputs));
    }
}
