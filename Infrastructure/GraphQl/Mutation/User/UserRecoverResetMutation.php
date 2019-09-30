<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Mutation\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Exception\FormValidationException;
use Xm\SymfonyBundle\Form\User\UserRecoverResetType;
use Xm\SymfonyBundle\Model\User\Command\ChangePassword;
use Xm\SymfonyBundle\Model\User\Command\VerifyUser;
use Xm\SymfonyBundle\Model\User\Exception\InvalidToken;
use Xm\SymfonyBundle\Model\User\Exception\TokenHasExpired;
use Xm\SymfonyBundle\Model\User\Token;
use Xm\SymfonyBundle\Security\PasswordEncoder;
use Xm\SymfonyBundle\Security\TokenValidator;

class UserRecoverResetMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var PasswordEncoder */
    private $passwordEncoder;

    /** @var TokenValidator */
    private $tokenValidator;

    public function __construct(
        MessageBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        PasswordEncoder $passwordEncoder,
        TokenValidator $tokenValidator
    ) {
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenValidator = $tokenValidator;
    }

    public function __invoke(Argument $args): array
    {
        $form = $this->formFactory
            ->create(UserRecoverResetType::class)
            ->submit([
                'token'       => $args['token'],
                'newPassword' => [
                    'first'  => $args['newPassword'],
                    'second' => $args['repeatPassword'],
                ],
            ]);

        if (!$form->isValid()) {
            throw FormValidationException::fromForm($form);
        }

        try {
            $user = $this->tokenValidator->validate(
                Token::fromString($form->getData()['token'])
            );
        } catch (InvalidToken $e) {
            // 404 -> not found
            throw new UserError('The token is invalid.', 404, $e);
        } catch (TokenHasExpired $e) {
            // 405 -> method not allowed
            throw new UserError('The link has expired.', 405);
        }

        if (!$user->verified()) {
            $this->commandBus->dispatch(
                VerifyUser::now($user->userId())
            );
        }

        $encodedPassword = ($this->passwordEncoder)(
            $user->firstRole(),
            $form->getData()['newPassword']
        );
        $this->commandBus->dispatch(
            ChangePassword::forUser($user->userId(), $encodedPassword)
        );

        // we would log the user in right away, but as we don't have a request
        // and the projection might not be caught up, we don't try

        return [
            'success' => true,
        ];
    }
}
