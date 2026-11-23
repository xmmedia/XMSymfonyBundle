<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\ProjectionReadModel::class, 'Xm\SymfonyBundle\Tests\ProjectionReadModel');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\ProjectionReadModel instead
     */
    trait ProjectionReadModel
    {
        use \Xm\SymfonyBundle\Test\ProjectionReadModel;
    }
}
