<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Command;

use Xm\SymfonyBundle\Model\User\Command\VerifyUserByAdmin;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class VerifyUserByAdminTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;

        $command = VerifyUserByAdmin::now($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}
