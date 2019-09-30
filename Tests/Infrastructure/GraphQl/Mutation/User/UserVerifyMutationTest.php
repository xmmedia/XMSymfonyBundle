<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Mutation\User;

use Mockery;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Entity\User;
use Xm\SymfonyBundle\Exception\FormValidationException;
use Xm\SymfonyBundle\Form\User\UserVerifyType;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Mutation\User\UserVerifyMutation;
use Xm\SymfonyBundle\Model\User\Command\ChangePassword;
use Xm\SymfonyBundle\Model\User\Command\VerifyUser;
use Xm\SymfonyBundle\Model\User\Exception\InvalidToken;
use Xm\SymfonyBundle\Model\User\Exception\TokenHasExpired;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Model\User\Token;
use Xm\SymfonyBundle\Security\PasswordEncoder;
use Xm\SymfonyBundle\Security\TokenValidator;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UserVerifyMutationTest extends BaseTestCase
{
    public function testValid(): void
    {
        $faker = $this->faker();
        $password = $faker->password;
        $data = [
            'token'          => $faker->password,
            'password'       => $password,
            'repeatPassword' => $password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(VerifyUser::class))
            ->andReturn(new Envelope(new \stdClass()));
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
            ->with(UserVerifyType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $passwordEncoder->shouldReceive('__invoke')
            ->once()
            ->andReturn('string');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnFalse();
        $user->shouldReceive('firstRole')
            ->once()
            ->andReturn(Role::ROLE_USER());

        $tokenValidator = Mockery::mock(TokenValidator::class);
        $tokenValidator->shouldReceive('validate')
            ->once()
            ->with(Mockery::type(Token::class))
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $result = (new UserVerifyMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder,
            $tokenValidator
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testAlreadyVerified(): void
    {
        $faker = $this->faker();
        $data = [
            'token'          => $faker->password,
            'password'       => $faker->password,
            'repeatPassword' => $faker->password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

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
            ->with(UserVerifyType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();

        $tokenValidator = Mockery::mock(TokenValidator::class);
        $tokenValidator->shouldReceive('validate')
            ->once()
            ->with(Mockery::type(Token::class))
            ->andReturn($user);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(404);

        $result = (new UserVerifyMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder,
            $tokenValidator
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testTokenExpired(): void
    {
        $faker = $this->faker();
        $data = [
            'token'          => $faker->password,
            'password'       => $faker->password,
            'repeatPassword' => $faker->password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

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
            ->with(UserVerifyType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);

        $tokenValidator = Mockery::mock(TokenValidator::class);
        $tokenValidator->shouldReceive('validate')
            ->once()
            ->with(Mockery::type(Token::class))
            ->andThrow(TokenHasExpired::before(new Token('string'), '24 hours'));

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(405);

        $result = (new UserVerifyMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder,
            $tokenValidator
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testTokenInvalid(): void
    {
        $faker = $this->faker();
        $data = [
            'token'          => $faker->password,
            'password'       => $faker->password,
            'repeatPassword' => $faker->password,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);

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
            ->with(UserVerifyType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);

        $tokenValidator = Mockery::mock(TokenValidator::class);
        $tokenValidator->shouldReceive('validate')
            ->once()
            ->with(Mockery::type(Token::class))
            ->andThrow(InvalidToken::tokenDoesntExist(new Token('string')));

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(UserError::class);
        $this->expectExceptionCode(404);

        $result = (new UserVerifyMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder,
            $tokenValidator
        ))($args);

        $this->assertEquals(['success' => true], $result);
    }

    public function testInvalid(): void
    {
        $faker = $this->faker();
        $data = [
            'token'          => $faker->password,
            'password'       => $faker->password,
            'repeatPassword' => $faker->password,
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
            ->with(UserVerifyType::class)
            ->andReturn($form);

        $passwordEncoder = Mockery::mock(PasswordEncoder::class);
        $tokenValidator = Mockery::mock(TokenValidator::class);

        $args = new Argument([
            'user' => $data,
        ]);

        $this->expectException(FormValidationException::class);

        (new UserVerifyMutation(
            $commandBus,
            $formFactory,
            $passwordEncoder,
            $tokenValidator
        ))($args);
    }
}
