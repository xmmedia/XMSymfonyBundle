<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Resolver\User;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Xm\SymfonyBundle\Entity\User;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Projection\User\UserFinder;

class UserResolver implements ResolverInterface
{
    /** @var UserFinder */
    private $userFinder;

    public function __construct(UserFinder $userFinder)
    {
        $this->userFinder = $userFinder;
    }

    public function __invoke(string $userId): ?User
    {
        return $this->userFinder->find(UserId::fromString($userId));
    }
}
