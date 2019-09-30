<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\Auth\Command;

use Xm\SymfonyBundle\Model\Auth\Command\UserLoggedInSuccessfully;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UserLoggedInSuccessfullyTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId;
        $userId = $faker->userId;
        $email = $faker->emailVo;
        $userAgent = $faker->userAgent;
        $ipAddress = $faker->ipv4;

        $command = UserLoggedInSuccessfully::now(
            $authId,
            $userId,
            $email,
            $userAgent,
            $ipAddress
        );

        $this->assertTrue($authId->sameValueAs($command->authId()));
        $this->assertEquals($userId, $command->userId());
        $this->assertEquals($email, $command->email());
        $this->assertEquals($userAgent, $command->userAgent());
        $this->assertEquals($ipAddress, $command->ipAddress());
    }
}
