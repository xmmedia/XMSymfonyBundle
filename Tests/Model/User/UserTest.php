<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User;

use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Model\User\User;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeAr;

class UserTest extends BaseTestCase
{
    use UserTestTrait;

    public function testSameIdentityAs(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $user1 = User::addByAdminMinimum(
            $userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );
        $user2 = User::addByAdminMinimum(
            $userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );

        $this->assertTrue($user1->sameIdentityAs($user2));
    }

    public function testSameIdentityAsFalse(): void
    {
        $faker = $this->faker();

        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $user1 = User::addByAdminMinimum(
            $faker->userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );
        $user2 = User::addByAdminMinimum(
            $faker->userId,
            $email,
            $password,
            $role,
            $this->userUniquenessCheckerNone
        );

        $this->assertFalse($user1->sameIdentityAs($user2));
    }

    public function testSameIdentityAsDiffClass(): void
    {
        $faker = $this->faker();

        $user = User::addByAdminMinimum(
            $faker->userId,
            $faker->emailVo,
            $faker->password,
            Role::ROLE_USER(),
            $this->userUniquenessCheckerNone
        );

        $this->assertFalse($user->sameIdentityAs(FakeAr::create()));
    }
}
