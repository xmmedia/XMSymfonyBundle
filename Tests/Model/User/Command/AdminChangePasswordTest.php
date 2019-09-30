<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Command;

use Xm\SymfonyBundle\Model\User\Command\AdminChangePassword;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class AdminChangePasswordTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $password = $faker->password;

        $command = AdminChangePassword::with($userId, $password);

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertEquals($password, $command->encodedPassword());
    }
}
