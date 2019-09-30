<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Mutation\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\Command\AdminAddUser;
use Xm\SymfonyBundle\Model\User\Name;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Security\PasswordEncoder;
use Xm\SymfonyBundle\Security\TokenGenerator;
use Xm\SymfonyBundle\Util\Assert;

class AdminUserAddMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var TokenGenerator */
    private $tokenGenerator;

    /** @var PasswordEncoder */
    private $passwordEncoder;

    public function __construct(
        MessageBusInterface $commandBus,
        TokenGenerator $tokenGenerator,
        PasswordEncoder $passwordEncoder
    ) {
        $this->commandBus = $commandBus;
        $this->tokenGenerator = $tokenGenerator;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function __invoke(Argument $args): array
    {
        $userId = UserId::fromString($args['user']['userId']);

        if (!$args['user']['setPassword']) {
            $password = ($this->tokenGenerator)()->toString();
        } else {
            $password = $args['user']['password'];
            // password checked here because it's encoded prior to the command
            Assert::passwordLength($password);
        }
        // check both generated & user entered,
        // though unlikely generated will be compromised
        Assert::compromisedPassword($password);

        $email = Email::fromString($args['user']['email']);
        $role = Role::byValue($args['user']['role']);

        $this->commandBus->dispatch(AdminAddUser::with(
            $userId,
            $email,
            ($this->passwordEncoder)($role, $password),
            $role,
            $args['user']['active'],
            Name::fromString($args['user']['firstName']),
            Name::fromString($args['user']['lastName']),
            $args['user']['sendInvite'],
        ));

        return [
            'userId' => $userId,
            'email'  => $email,
            'active' => $args['user']['active'],
        ];
    }
}
