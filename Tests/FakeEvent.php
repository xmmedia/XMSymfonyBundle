<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\FakeEvent::class, 'Xm\SymfonyBundle\Tests\FakeEvent');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\FakeEvent instead
     */
    class FakeEvent extends \Xm\SymfonyBundle\Test\FakeEvent
    {
    }
}
