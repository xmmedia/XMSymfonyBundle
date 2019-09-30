<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Mutation\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\Command\AdminChangePassword;
use Xm\SymfonyBundle\Model\User\Command\AdminUpdateUser;
use Xm\SymfonyBundle\Model\User\Name;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Security\PasswordEncoder;
use Xm\SymfonyBundle\Util\Assert;

class AdminUserUpdateMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var PasswordEncoder */
    private $passwordEncoder;

    public function __construct(
        MessageBusInterface $commandBus,
        PasswordEncoder $passwordEncoder
    ) {
        $this->commandBus = $commandBus;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function __invoke(Argument $args): array
    {
        $userId = UserId::fromString($args['user']['userId']);

        if ($args['user']['setPassword']) {
            $password = $args['user']['password'];
            // password checked here because it's encoded prior to the command
            Assert::passwordLength($password);
            Assert::compromisedPassword($password);
        }

        $role = Role::byValue($args['user']['role']);

        $this->commandBus->dispatch(AdminUpdateUser::with(
            $userId,
            Email::fromString($args['user']['email']),
            $role,
            Name::fromString($args['user']['firstName']),
            Name::fromString($args['user']['lastName']),
        ));

        if ($args['user']['setPassword']) {
            $encodedPassword = ($this->passwordEncoder)($role, $password);

            $this->commandBus->dispatch(
                AdminChangePassword::with(
                    $userId,
                    $encodedPassword
                )
            );
        }

        return [
            'userId' => $userId,
        ];
    }
}
