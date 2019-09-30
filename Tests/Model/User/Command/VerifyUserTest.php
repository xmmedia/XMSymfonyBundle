<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Command;

use Xm\SymfonyBundle\Model\User\Command\VerifyUser;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class VerifyUserTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;

        $command = VerifyUser::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}
