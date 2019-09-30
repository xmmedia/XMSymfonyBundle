<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Mutation\User;

use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Mutation\User\AdminUserAddMutation;
use Xm\SymfonyBundle\Model\User\Command\AdminAddUser;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Model\User\Token;
use Xm\SymfonyBundle\Security\PasswordEncoder;
use Xm\SymfonyBundle\Security\TokenGenerator;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class AdminUserAddMutationTest extends BaseTestCase
{
    public function testGeneratePassword(): void
    {
        $faker = $this->faker();
        $data = [
            'userId'      => $faker->uuid,
            'email'       => $faker->email,
            'setPassword' => false,
            'firstName'   => $faker->name,
            'lastName'    => $faker->name,
            'role'        => 'ROLE_USER',
            'active'      => true,
            'sendInvite'  => true,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AdminAddUser::class))
            ->andReturn(new Envelope(new \stdClass()));

        $tokenGenerator = Mockery::mock(TokenGenerator::class);
        $tokenGenerator->shouldReceive('__invoke')
            ->once()
            ->andReturn(new Token($faker->password));

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $passwordEncoder->shouldReceive('__invoke')
            ->once()
            ->andReturn('string');

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserAddMutation(
            $commandBus,
            $tokenGenerator,
            $passwordEncoder
        ))($args);

        $expected = [
            'userId' => $data['userId'],
            'email'  => $data['email'],
            'active' => $data['active'],
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
            'active'      => true,
            'sendInvite'  => true,
        ];

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(\InvalidArgumentException::class);

        (new AdminUserAddMutation(
            Mockery::mock(MessageBusInterface::class),
            Mockery::mock(TokenGenerator::class),
            Mockery::mock(PasswordEncoder::class)
        ))($args);
    }

    public function testSetPassword(): void
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
            'active'      => true,
            'sendInvite'  => true,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(AdminAddUser::class))
            ->andReturn(new Envelope(new \stdClass()));

        $tokenGenerator = Mockery::mock(TokenGenerator::class);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $passwordEncoder->shouldReceive('__invoke')
            ->once()
            ->with(Mockery::type(Role::class), $data['password'])
            ->andReturn('string');

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new AdminUserAddMutation(
            $commandBus,
            $tokenGenerator,
            $passwordEncoder
        ))($args);

        $expected = [
            'userId' => $data['userId'],
            'email'  => $data['email'],
            'active' => $data['active'],
        ];

        $this->assertEquals($expected, $result);
    }
}
