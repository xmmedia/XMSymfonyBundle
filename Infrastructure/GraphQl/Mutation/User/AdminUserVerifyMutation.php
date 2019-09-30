<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Mutation\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\User\Command\VerifyUserByAdmin;
use Xm\SymfonyBundle\Model\User\UserId;

class AdminUserVerifyMutation implements MutationInterface
{
    /** @var MessageBusInterface */
    private $commandBus;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function __invoke(Argument $args): array
    {
        $userId = UserId::fromString($args['user']['userId']);

        $this->commandBus->dispatch(VerifyUserByAdmin::now($userId));

        return [
            'userId' => $userId,
        ];
    }
}
