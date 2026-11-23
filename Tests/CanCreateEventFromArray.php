<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\CanCreateEventFromArray::class, 'Xm\SymfonyBundle\Tests\CanCreateEventFromArray');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\CanCreateEventFromArray instead
     */
    trait CanCreateEventFromArray
    {
        use \Xm\SymfonyBundle\Test\CanCreateEventFromArray;
    }
}
