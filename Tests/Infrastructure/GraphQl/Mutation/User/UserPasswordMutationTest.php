<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Mutation\User;

use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Xm\SymfonyBundle\Exception\FormValidationException;
use Xm\SymfonyBundle\Form\User\UserChangePasswordType;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Mutation\User\UserPasswordMutation;
use Xm\SymfonyBundle\Model\User\Command\ChangePassword;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Security\PasswordEncoder;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateSecurityTrait;

class UserPasswordMutationTest extends BaseTestCase
{
    use CanCreateSecurityTrait;

    public function testValid(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId;
        $data = [
            'currentPassword' => $faker->password,
            'newPassword'     => $faker->password,
            'repeatPassword'  => $faker->password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(ChangePassword::class))
            ->andReturn(new Envelope(new \stdClass()));

        $form = Mockery::mock(FormInterface::class);
        $form->shouldReceive('submit')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturnSelf();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturnTrue();
        $form->shouldReceive('getData')
            ->andReturn($data);
        $formFactory = Mockery::mock(FormFactoryInterface::class);
        $formFactory->shouldReceive('create')
            ->with(UserChangePasswordType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $passwordEncoder->shouldReceive('__invoke')
            ->once()
            ->andReturn('string');

        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('userId')
            ->atLeast()
            ->times(2)
            ->andReturn($userId);
        $user->shouldReceive('firstRole')
            ->once()
            ->andReturn(Role::ROLE_USER());
        $security = $this->createSecurity($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new UserPasswordMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder,
            $security
        ))($args);

        $expected = [
            'userId' => $userId->toString(),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testInvalid(): void
    {
        $faker = $this->faker();
        $data = [
            'currentPassword' => $faker->password,
            'newPassword'     => $faker->password,
            'repeatPassword'  => $faker->password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

        $form = Mockery::mock(FormInterface::class);
        $form->shouldReceive('submit')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturnSelf();
        $form->shouldReceive('isValid')
            ->once()
            ->andReturnFalse();
        $formFactory = Mockery::mock(FormFactoryInterface::class);
        $formFactory->shouldReceive('create')
            ->with(UserChangePasswordType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(UserInterface::class);
        $security = $this->createSecurity($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(FormValidationException::class);

        (new UserPasswordMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder,
            $security
        ))($args);
    }
}
