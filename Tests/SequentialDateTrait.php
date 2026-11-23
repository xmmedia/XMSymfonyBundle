<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\SequentialDateTrait::class, 'Xm\SymfonyBundle\Tests\SequentialDateTrait');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\SequentialDateTrait instead
     */
    trait SequentialDateTrait
    {
        use \Xm\SymfonyBundle\Test\SequentialDateTrait;
    }
}
