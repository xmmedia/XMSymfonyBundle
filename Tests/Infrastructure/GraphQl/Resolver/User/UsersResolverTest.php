<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Resolver\User;

use Mockery;
use Xm\SymfonyBundle\Entity\User;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Resolver\User\UsersResolver;
use Xm\SymfonyBundle\Projection\User\UserFinder;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UsersResolverTest extends BaseTestCase
{
    public function test(): void
    {
        $user = Mockery::mock(User::class);

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findByUserFilters')
            ->once()
            ->andReturn([$user]);

        $resolver = new UsersResolver($userFinder);

        $result = $resolver([]);

        $this->assertEquals([$user], $result);
    }

    public function testNoneFound(): void
    {
        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('findByUserFilters')
            ->once()
            ->andReturn([]);

        $resolver = new UsersResolver($userFinder);

        $this->assertEquals([], $resolver([]));
    }
}
