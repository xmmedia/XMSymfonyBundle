<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\FakeAr::class, 'Xm\SymfonyBundle\Tests\FakeAr');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\FakeAr instead
     */
    class FakeAr extends \Xm\SymfonyBundle\Test\FakeAr
    {
    }
}
