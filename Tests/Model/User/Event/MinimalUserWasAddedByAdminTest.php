<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Event;

use Xm\SymfonyBundle\Model\User\Event\MinimalUserWasAddedByAdmin;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class MinimalUserWasAddedByAdminTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $event = MinimalUserWasAddedByAdmin::now($userId, $email, $password, $role);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($password, $event->encodedPassword());
        $this->assertEquals($role, $event->role());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        /** @var MinimalUserWasAddedByAdmin $event */
        $event = $this->createEventFromArray(
            MinimalUserWasAddedByAdmin::class,
            $userId->toString(),
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
            ]
        );

        $this->assertInstanceOf(MinimalUserWasAddedByAdmin::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($email, $event->email());
        $this->assertEquals($password, $event->encodedPassword());
        $this->assertEquals($role, $event->role());
    }
}
