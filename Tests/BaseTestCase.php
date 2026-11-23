<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\BaseTestCase::class, 'Xm\SymfonyBundle\Tests\BaseTestCase');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\BaseTestCase instead
     */
    class BaseTestCase extends \Xm\SymfonyBundle\Test\BaseTestCase
    {
    }
}
