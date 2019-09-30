<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\Auth\Handler;

use Mockery;
use Xm\SymfonyBundle\Model\Auth\Auth;
use Xm\SymfonyBundle\Model\Auth\AuthList;
use Xm\SymfonyBundle\Model\Auth\Command\UserLoggedInSuccessfully;
use Xm\SymfonyBundle\Model\Auth\Handler\UserLoggedInSuccessfullyHandler;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UserLoggedInSuccessfullyHandlerTest extends BaseTestCase
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

        $repo = Mockery::mock(AuthList::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(Auth::class));

        (new UserLoggedInSuccessfullyHandler($repo))($command);
    }
}
