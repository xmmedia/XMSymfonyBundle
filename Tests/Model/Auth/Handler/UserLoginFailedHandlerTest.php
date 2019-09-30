<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\Auth\Handler;

use Mockery;
use Xm\SymfonyBundle\Model\Auth\Auth;
use Xm\SymfonyBundle\Model\Auth\AuthList;
use Xm\SymfonyBundle\Model\Auth\Command\UserLoginFailed;
use Xm\SymfonyBundle\Model\Auth\Handler\UserLoginFailedHandler;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UserLoginFailedHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $authId = $faker->authId;
        $email = $faker->email;
        $userAgent = $faker->userAgent;
        $ipAddress = $faker->ipv4;
        $message = $faker->asciify(str_repeat('*', 100));

        $command = UserLoginFailed::now(
            $authId,
            $email,
            $userAgent,
            $ipAddress,
            $message
        );

        $repo = Mockery::mock(AuthList::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(Auth::class));

        (new UserLoginFailedHandler($repo))($command);
    }
}
