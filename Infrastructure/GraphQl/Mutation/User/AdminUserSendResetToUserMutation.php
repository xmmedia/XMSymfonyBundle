<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Mutation\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\User\Command\InitiatePasswordRecovery;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Projection\User\UserFinder;

class AdminUserSendResetToUserMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    /** @var UserFinder */
    private $userFinder;

    public function __construct(
        MessageBusInterface $commandBus,
        UserFinder $userFinder
    ) {
        $this->commandBus = $commandBus;
        $this->userFinder = $userFinder;
    }

    public function __invoke(Argument $args): array
    {
        $userId = UserId::fromString($args['user']['userId']);

        $user = $this->userFinder->find($userId);
        if (!$user) {
            throw new UserError('The user could not be found.');
        }

        $this->commandBus->dispatch(
            InitiatePasswordRecovery::now($user->userId(), $user->email())
        );

        return [
            'userId' => $userId,
        ];
    }
}
