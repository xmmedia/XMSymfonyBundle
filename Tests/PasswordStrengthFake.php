<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Xm\SymfonyBundle\Util\PasswordStrengthInterface;

class PasswordStrengthFake implements PasswordStrengthInterface
{
    private $score;

    public function __construct(int $score = 4)
    {
        $this->score = $score;
    }

    public function __invoke(string $password, array $userInputs = []): array
    {
        return [
            'score'    => $this->score,
            'password' => $password,
        ];
    }
}
