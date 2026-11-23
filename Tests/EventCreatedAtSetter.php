<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\EventCreatedAtSetter::class, 'Xm\SymfonyBundle\Tests\EventCreatedAtSetter');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\EventCreatedAtSetter instead
     */
    trait EventCreatedAtSetter
    {
        use \Xm\SymfonyBundle\Test\EventCreatedAtSetter;
    }
}
