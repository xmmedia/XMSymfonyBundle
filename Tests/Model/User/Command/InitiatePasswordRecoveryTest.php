<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Command;

use Xm\SymfonyBundle\Model\User\Command\InitiatePasswordRecovery;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class InitiatePasswordRecoveryTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;

        $command = InitiatePasswordRecovery::now($userId, $email);

        $this->assertTrue($userId->sameValueAs($command->userId()));
        $this->assertTrue($email->sameValueAs($command->email()));
    }
}
