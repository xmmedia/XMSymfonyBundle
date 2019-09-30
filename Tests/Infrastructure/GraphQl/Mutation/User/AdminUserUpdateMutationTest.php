<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Mutation\User;

use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Mutation\User\AdminUserUpdateMutation;
use Xm\SymfonyBundle\Model\User\Command\AdminChangePassword;
use Xm\SymfonyBundle\Model\User\Command\AdminUpdateUser;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Security\PasswordEncoder;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class AdminUserUpdateMutationTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'      => $faker->uuid,
            'email'       => $faker->email,
            'setPassword' => false,
            'firstName'   => $faker->name,
            'lastName'    => $faker->name,
            'role'        => 'ROLE_USER',
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AdminUpdateUser::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserUpdateMutation(
            $commandBus,
            $passwordEncoder
        ))($args);

        $expected = [
            'userId' => $data['userId'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testChangePassword(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'      => $faker->uuid,
            'email'       => $faker->email,
            'setPassword' => true,
            'password'    => $faker->password,
            'firstName'   => $faker->name,
            'lastName'    => $faker->name,
            'role'        => 'ROLE_USER',
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AdminUpdateUser::class))
            ->andReturn(new Envelope(new \stdClass()));
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AdminChangePassword::class))
            ->andReturn(new Envelope(new \stdClass()));

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $passwordEncoder->shouldReceive('__invoke')
            ->once()
            ->with(Mockery::type(Role::class), $data['password'])
            ->andReturn('string');

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserUpdateMutation(
            $commandBus,
            $passwordEncoder
        ))(
            $args
        );

        $expected = [
            'userId' => $data['userId'],
        ];

        $this->assertEquals($expected, $result);
    }

    public function testPasswordTooLong(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'      => $faker->uuid,
            'email'       => $faker->email,
            'setPassword' => true,
            'password'    => $faker->string(4097),
            'firstName'   => $faker->name,
            'lastName'    => $faker->name,
            'role'        => 'ROLE_USER',
        ];

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new AdminUserUpdateMutation(
            Mockery::mock(MessageBusInterface::class),
            Mockery::mock(PasswordEncoder::class)
        ))(
            $args
        );
    }
}
