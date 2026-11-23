<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

class_alias(\Xm\SymfonyBundle\Test\VoterTestTrait::class, 'Xm\SymfonyBundle\Tests\VoterTestTrait');

if (false) {
    /**
     * @deprecated since xm/symfony-bundle 2.1, use \Xm\SymfonyBundle\Test\VoterTestTrait instead
     */
    trait VoterTestTrait
    {
        use \Xm\SymfonyBundle\Test\VoterTestTrait;
    }
}
