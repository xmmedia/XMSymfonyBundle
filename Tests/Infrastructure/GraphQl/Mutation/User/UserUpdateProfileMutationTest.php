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
use Xm\SymfonyBundle\Form\User\UserProfileType;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Mutation\User\UserUpdateProfileMutation;
use Xm\SymfonyBundle\Model\User\Command\UpdateUserProfile;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateSecurityTrait;

class UserUpdateProfileMutationTest extends BaseTestCase
{
    use CanCreateSecurityTrait;

    public function testValid(): void
    {
        $faker = $this->faker();
        $userId = $faker->userId;
        $data = [
            'email'     => $faker->email,
            'firstName' => $faker->name,
            'lastName'  => $faker->name,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(UpdateUserProfile::class))
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
            ->with(UserProfileType::class)
            ->andReturn($form);

        $user = Mockery::mock(UserInterface::class);
        $user->shouldReceive('userId')
            ->atLeast()
            ->times(2)
            ->andReturn($userId);
        $security = $this->createSecurity($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new UserUpdateProfileMutation(
            $commandBus,
            $formFactory,
            $security
        ))($args);

        $expected = [
            'userId' => $userId->toString(),
        ];

        $this->assertEquals($expected, $result);
    }

    public function testInvalid(): void
    {
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
            ->with(UserProfileType::class)
            ->andReturn($form);

        $user = Mockery::mock(UserInterface::class);
        $security = $this->createSecurity($user);

        $args = new Argument([
            'user' => [],
        ]);

        $this->expectException(FormValidationException::class);

        (new UserUpdateProfileMutation(
            $commandBus,
            $formFactory,
            $security
        ))($args);
    }
}
