<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Handler;

use Mockery;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\Command\AdminUpdateUser;
use Xm\SymfonyBundle\Model\User\Exception\UserNotFound;
use Xm\SymfonyBundle\Model\User\Handler\AdminUpdateUserHandler;
use Xm\SymfonyBundle\Model\User\Name;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Model\User\Service\ChecksUniqueUsersEmail;
use Xm\SymfonyBundle\Model\User\User;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Model\User\UserIdInterface;
use Xm\SymfonyBundle\Model\User\UserList;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class AdminUpdateUserHandlerTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $command = AdminUpdateUser::with(
            $userId,
            $email,
            $role,
            $firstName,
            $lastName
        );

        $user = Mockery::mock(User::class);
        $user->shouldReceive('updateByAdmin')
            ->once();

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturn($user);
        $repo->shouldReceive('save')
            ->once()
            ->with(Mockery::type(User::class));

        (new AdminUpdateUserHandler(
            $repo,
            new AdminUpdateUserHandlerUniquenessCheckerNone()
        ))(
            $command
        );
    }

    public function testNotFound(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $command = AdminUpdateUser::with(
            $userId,
            $email,
            $role,
            $firstName,
            $lastName
        );

        $repo = Mockery::mock(UserList::class);
        $repo->shouldReceive('get')
            ->with(Mockery::type(UserId::class))
            ->andReturnNull();

        $this->expectException(UserNotFound::class);

        (new AdminUpdateUserHandler(
            $repo,
            new AdminUpdateUserHandlerUniquenessCheckerNone()
        ))(
            $command
        );
    }
}

class AdminUpdateUserHandlerUniquenessCheckerNone implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserIdInterface
    {
        return null;
    }
}
