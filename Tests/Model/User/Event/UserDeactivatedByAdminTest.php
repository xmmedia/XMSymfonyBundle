<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Event;

use Xm\SymfonyBundle\Model\User\Event\UserDeactivatedByAdmin;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class UserDeactivatedByAdminTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;

        $event = UserDeactivatedByAdmin::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;

        /** @var UserDeactivatedByAdmin $event */
        $event = $this->createEventFromArray(
            UserDeactivatedByAdmin::class,
            $userId->toString()
        );

        $this->assertInstanceOf(UserDeactivatedByAdmin::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
