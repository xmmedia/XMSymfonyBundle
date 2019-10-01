<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Handler;

use Mockery;
use Ramsey\Uuid\Uuid;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\Command\AdminAddUserMinimum;
use Xm\SymfonyBundle\Model\User\Exception\DuplicateEmail;
use Xm\SymfonyBundle\Model\User\Handler\AdminAddUserMinimumHandler;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Model\User\Service\ChecksUniqueUsersEmail;
use Xm\SymfonyBundle\Model\User\User;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Model\User\UserIdInterface;
use Xm\SymfonyBundle\Model\User\UserList;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class AdminAddUserMinimumHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $command = AdminAddUserMinimum::with(
            $userId,
            $email,
            $password,
            $role
        );

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        (new AdminAddUserMinimumHandler(
            $repo,
            new AdminAddUserMinimumHandlerUniquenessCheckerNone()
        ))(
            $command
        );
    }

    public function testNonUnique(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();

        $command = AdminAddUserMinimum::with(
            $userId,
            $email,
            $password,
            $role
        );

        $repo = Mockery::mock(UserList::class);

        $this->expectException(DuplicateEmail::class);

        (new AdminAddUserMinimumHandler(
            $repo,
            new AdminAddUserMinimumHandlerUniquenessCheckerDuplicate()
        ))(
            $command
        );
    }
}

class AdminAddUserMinimumHandlerUniquenessCheckerNone implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserIdInterface
    {
        return null;
    }
}

class AdminAddUserMinimumHandlerUniquenessCheckerDuplicate implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserIdInterface
    {
        return UserId::fromUuid(Uuid::uuid4());
    }
}
