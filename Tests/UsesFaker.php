<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\UsesFaker::class, 'Xm\SymfonyBundle\Tests\UsesFaker');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\UsesFaker instead
     */
    trait UsesFaker
    {
        use \Xm\SymfonyBundle\Test\UsesFaker;
    }
}
