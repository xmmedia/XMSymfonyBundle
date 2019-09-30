<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Command;

use Xm\SymfonyBundle\Model\User\Command\DeactivateUserByAdmin;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class DeactivateUserByAdminTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;

        $command = DeactivateUserByAdmin::user($userId);

        $this->assertTrue($userId->sameValueAs($command->userId()));
    }
}
