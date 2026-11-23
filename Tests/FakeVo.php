<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\FakeVo::class, 'Xm\SymfonyBundle\Tests\FakeVo');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\FakeVo instead
     */
    class FakeVo extends \Xm\SymfonyBundle\Test\FakeVo
    {
    }
}
