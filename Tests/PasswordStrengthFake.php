<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\PasswordStrengthFake::class, 'Xm\SymfonyBundle\Tests\PasswordStrengthFake');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\PasswordStrengthFake instead
     */
    class PasswordStrengthFake extends \Xm\SymfonyBundle\Test\PasswordStrengthFake
    {
    }
}
