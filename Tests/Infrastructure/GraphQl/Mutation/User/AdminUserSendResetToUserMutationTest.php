<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Mutation\User;

use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Entity\User;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Mutation\User\AdminUserSendResetToUserMutation;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\Command\InitiatePasswordRecovery;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Projection\User\UserFinder;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class AdminUserSendResetToUserMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $data = [
            'user' => ['userId' => $faker->uuid],
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(InitiatePasswordRecovery::class))
            ->andReturn(new Envelope(new \stdClass()));

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->once()
            ->andReturn($faker->userId);
        $user->shouldReceive('email')
            ->once()
            ->andReturn(Email::fromString($faker->email));

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);

        $args = new Argument($data);

        $result = (new AdminUserSendResetToUserMutation(
            $commandBus,
            $userFinder
        ))($args);

        $this->assertEquals(['userId' => $data['user']['userId']], $result);
    }

    public function testUserNotFound(): void
    {
        $faker = $this->faker();
        $data = [
            'user' => ['userId' => $faker->uuid],
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $userFinder = Mockery::mock(UserFinder::class);
        $userFinder->shouldReceive('find')
            ->once()
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $args = new Argument($data);

        $this->expectException(UserError::class);

        (new AdminUserSendResetToUserMutation(
            $commandBus,
            $userFinder
        ))($args);
    }
}
