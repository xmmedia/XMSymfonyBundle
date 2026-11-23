<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\TestUserEntity::class, 'Xm\SymfonyBundle\Tests\TestUserEntity');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\TestUserEntity instead
     */
    class TestUserEntity extends \Xm\SymfonyBundle\Test\TestUserEntity
    {
    }
}
