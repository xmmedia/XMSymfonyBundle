<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User;

use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UserIdTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $uuid = $faker->uuid;

        $userId = UserId::fromString($uuid);

        $this->assertEquals($uuid, $userId->toString());
    }
}
