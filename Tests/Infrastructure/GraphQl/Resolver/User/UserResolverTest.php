<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Resolver\User;

use Mockery;
use Xm\SymfonyBundle\Entity\User;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Resolver\User\UserResolver;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Projection\User\UserFinder;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UserResolverTest extends BaseTestCase
{
    public function testUserByUserId(): void
    {
        $faker = $this->faker();

        $userId = $faker->uuid;
        $user = Mockery::mock(User::class);

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);

        $resolver = new UserResolver($userFinder);

        $result = $resolver($userId);

        $this->assertEquals($user, $result);
    }

    public function testUserByUserIdNotFound(): void
    {
        $faker = $this->faker();

        $userId = $faker->uuid;

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $resolver = new UserResolver($userFinder);

        $result = $resolver($userId);

        $this->assertNull($result);
    }
}
