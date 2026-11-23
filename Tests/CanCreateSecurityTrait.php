<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\CanCreateSecurityTrait::class, 'Xm\SymfonyBundle\Tests\CanCreateSecurityTrait');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\CanCreateSecurityTrait instead
     */
    trait CanCreateSecurityTrait
    {
        use \Xm\SymfonyBundle\Test\CanCreateSecurityTrait;
    }
}
