<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Util;

interface PasswordStrengthInterface
{
    public function __invoke(string $password, array $userInputs = []): array;
}
