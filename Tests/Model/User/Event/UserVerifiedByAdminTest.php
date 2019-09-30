<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Event;

use Xm\SymfonyBundle\Model\User\Event\UserVerifiedByAdmin;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class UserVerifiedByAdminTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;

        $event = UserVerifiedByAdmin::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;

        /** @var UserVerifiedByAdmin $event */
        $event = $this->createEventFromArray(
            UserVerifiedByAdmin::class,
            $userId->toString()
        );

        $this->assertInstanceOf(UserVerifiedByAdmin::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
