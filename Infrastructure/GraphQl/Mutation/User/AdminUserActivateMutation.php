<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Mutation\User;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\Model\User\Command\ActivateUserByAdmin;
use Xm\SymfonyBundle\Model\User\Command\DeactivateUserByAdmin;
use Xm\SymfonyBundle\Model\User\UserId;

class AdminUserActivateMutation implements MutationInterface
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
        $action = strtolower($args['user']['action']);

        switch ($action) {
            case 'activate':
                $command = ActivateUserByAdmin::class;
                break;
            case 'deactivate':
                $command = DeactivateUserByAdmin::class;
                break;
            default:
                throw new UserError(sprintf('The "%s" action is invalid.', $action));
        }

        $this->commandBus->dispatch($command::user($userId));

        return [
            'userId' => $userId,
        ];
    }
}
