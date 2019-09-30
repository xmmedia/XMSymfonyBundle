<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Mutation\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Security;
use Xm\SymfonyBundle\Exception\FormValidationException;
use Xm\SymfonyBundle\Form\User\UserChangePasswordType;
use Xm\SymfonyBundle\Model\User\Command\ChangePassword;
use Xm\SymfonyBundle\Security\PasswordEncoder;

class UserPasswordMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var FormFactoryInterface */
    private $formFactory;

    /** @var PasswordEncoder */
    private $passwordEncoder;

    /** @var Security */
    private $security;

    public function __construct(
        MessageBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        PasswordEncoder $passwordEncoder,
        Security $security
    ) {
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->passwordEncoder = $passwordEncoder;
        $this->security = $security;
    }

    public function __invoke(Argument $args): array
    {
        $form = $this->formFactory
            ->create(UserChangePasswordType::class)
            ->submit([
                'currentPassword' => $args['user']['currentPassword'],
                'newPassword'     => [
                    'first'  => $args['user']['newPassword'],
                    'second' => $args['user']['repeatPassword'],
                ],
            ]);

        if (!$form->isValid()) {
            throw FormValidationException::fromForm($form, 'user');
        }

        $encodedPassword = ($this->passwordEncoder)(
            $this->security->getUser()->firstRole(),
            $form->getData()['newPassword']
        );

        $this->commandBus->dispatch(
            ChangePassword::forUser(
                $this->security->getUser()->userId(),
                $encodedPassword
            )
        );

        return [
            'userId' => $this->security->getUser()->userId(),
        ];
    }
}
