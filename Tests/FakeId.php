<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\FakeId::class, 'Xm\SymfonyBundle\Tests\FakeId');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\FakeId instead
     */
    class FakeId extends \Xm\SymfonyBundle\Test\FakeId
    {
    }
}
