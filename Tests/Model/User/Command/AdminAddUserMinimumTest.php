<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Command;

use Xm\SymfonyBundle\Model\User\Command\AdminAddUserMinimum;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class AdminAddUserMinimumTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $command = AdminAddUserMinimum::with(
            $userId,
            $email,
            $password,
            $role
        );

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertTrue($email->sameValueAs($command->email()));
        $this->assertEquals($password, $command->encodedPassword());
        $this->assertEquals($role, $command->role());
    }
}
