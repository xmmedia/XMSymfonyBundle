<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Event;

use Xm\SymfonyBundle\Model\User\Event\AdminChangedPassword;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class AdminChangedPasswordTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $password = $faker->password;

        $event = AdminChangedPassword::now($userId, $password);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($password, $event->encodedPassword());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $password = $faker->password;

        /** @var AdminChangedPassword $event */
        $event = $this->createEventFromArray(
            AdminChangedPassword::class,
            $userId->toString(),
            [
                'encodedPassword' => $password,
            ]
        );

        $this->assertInstanceOf(AdminChangedPassword::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($password, $event->encodedPassword());
    }
}
