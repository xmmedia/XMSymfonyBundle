<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias('Xm\SymfonyBundle\Test\UserAuthMock', 'Xm\SymfonyBundle\Tests\UserAuthMock');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\UserAuthMock instead
     */
    trait UserAuthMock
    {
        use \Xm\SymfonyBundle\Test\UserAuthMock;
    }
}
