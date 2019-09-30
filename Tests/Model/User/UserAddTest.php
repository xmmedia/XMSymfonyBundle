<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User;

use Xm\SymfonyBundle\Model\User\Event;
use Xm\SymfonyBundle\Model\User\Exception;
use Xm\SymfonyBundle\Model\User\Name;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Model\User\User;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UserAddTest extends BaseTestCase
{
    use UserTestTrait;

    public function testAddByAdmin(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::addByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            false,
            $this->userUniquenessCheckerNone
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasAddedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
                'active'          => true,
                'firstName'       => $firstName->toString(),
                'lastName'        => $lastName->toString(),
                'sendInvite'      => false,
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertTrue($user->active());
    }

    public function testAddByAdminSendInvite(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::addByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerNone
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasAddedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
                'active'          => true,
                'firstName'       => $firstName->toString(),
                'lastName'        => $lastName->toString(),
                'sendInvite'      => true,
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertFalse($user->verified());
        $this->assertTrue($user->active());
    }

    public function testAddByAdminNotActive(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::addByAdmin(
            $userId,
            $email,
            $password,
            $role,
            false,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerNone
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserWasAddedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
                'active'          => false,
                'firstName'       => $firstName->toString(),
                'lastName'        => $lastName->toString(),
                'sendInvite'      => false,
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertFalse($user->active());
    }

    public function testAddByAdminDuplicateEmail(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $this->expectException(Exception\DuplicateEmail::class);

        User::addByAdmin(
            $userId,
            $email,
            $password,
            $role,
            true,
            $firstName,
            $lastName,
            true,
            $this->userUniquenessCheckerDuplicate
        );
    }

    public function testAddByAdminMinimal(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $user = User::addByAdminMinimum(
            $userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );

        $this->assertInstanceOf(User::class, $user);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\MinimalUserWasAddedByAdmin::class,
            [
                'email'           => $email->toString(),
                'encodedPassword' => $password,
                'role'            => $role->getValue(),
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertEquals($userId, $user->userId());
        $this->assertTrue($user->verified());
        $this->assertTrue($user->active());
    }

    public function testAddByAdminMinimalDuplicate(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $this->expectException(Exception\DuplicateEmail::class);

        User::addByAdminMinimum(
            $userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerDuplicate
        );
    }
}
