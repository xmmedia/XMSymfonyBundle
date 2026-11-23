<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\MessengerMiddlewareTestTrait::class, 'Xm\SymfonyBundle\Tests\MessengerMiddlewareTestTrait');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\MessengerMiddlewareTestTrait instead
     */
    trait MessengerMiddlewareTestTrait
    {
        use \Xm\SymfonyBundle\Test\MessengerMiddlewareTestTrait;
    }
}
