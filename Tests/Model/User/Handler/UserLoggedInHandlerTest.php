<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Handler;

use Mockery;
use Xm\SymfonyBundle\Model\User\Command\UserLoggedIn;
use Xm\SymfonyBundle\Model\User\Exception\UserNotFound;
use Xm\SymfonyBundle\Model\User\Handler\UserLoggedInHandler;
use Xm\SymfonyBundle\Model\User\User;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Model\User\UserList;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UserLoggedInHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('loggedIn')
            ->once();

        $command = UserLoggedIn::now($faker->userId);

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        (new UserLoggedInHandler($repo))($command);
    }

    public function testUserNotFound(): void
    {
        $faker = $this->faker();

        $command = UserLoggedIn::now($faker->userId);

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $this->expectException(UserNotFound::class);

        (new UserLoggedInHandler($repo))($command);
    }
}
