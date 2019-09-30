<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\GraphQl\Resolver\User;

use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Xm\SymfonyBundle\Entity\User;
use Xm\SymfonyBundle\Projection\User\UserFilters;
use Xm\SymfonyBundle\Projection\User\UserFinder;

class UsersResolver implements ResolverInterface
{
    /** @var UserFinder */
    private $userFinder;

    public function __construct(UserFinder $userFinder)
    {
        $this->userFinder = $userFinder;
    }

    /**
     * @return User[]
     */
    public function __invoke(?array $filters): array
    {
        return $this->userFinder->findByUserFilters(
            UserFilters::fromArray($filters)
        );
    }
}
