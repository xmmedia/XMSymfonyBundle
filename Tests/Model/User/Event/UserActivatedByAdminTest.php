<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Event;

use Xm\SymfonyBundle\Model\User\Event\UserActivatedByAdmin;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class UserActivatedByAdminTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;

        $event = UserActivatedByAdmin::now($userId);

        $this->assertEquals($userId, $event->userId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;

        /** @var UserActivatedByAdmin $event */
        $event = $this->createEventFromArray(
            UserActivatedByAdmin::class,
            $userId->toString()
        );

        $this->assertInstanceOf(UserActivatedByAdmin::class, $event);

        $this->assertEquals($userId, $event->userId());
    }
}
