<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User;

use Mockery;
use Ramsey\Uuid\Uuid;
use Xm\SymfonyBundle\Infrastructure\Service\ChecksUniqueUsersEmailFromReadModel;
use Xm\SymfonyBundle\Model\User\Name;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Model\User\User;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Projection\User\UserFinder;

trait UserTestTrait
{
    /** @var ChecksUniqueUsersEmailFromReadModel|\Mockery\MockInterface */
    private $userUniquenessCheckerNone;

    /** @var ChecksUniqueUsersEmailFromReadModel|\Mockery\MockInterface */
    private $userUniquenessCheckerDuplicate;

    protected function setUp(): void
    {
        $this->userUniquenessCheckerNone = Mockery::spy(
            new ChecksUniqueUsersEmailFromReadModel(
                Mockery::mock(UserFinder::class)
            )
        );
        $this->userUniquenessCheckerNone->shouldReceive('__invoke')
            ->andReturnNull()
            ->byDefault();

        $this->userUniquenessCheckerDuplicate = Mockery::spy(
            new ChecksUniqueUsersEmailFromReadModel(
                Mockery::mock(UserFinder::class)
            )
        );
        $this->userUniquenessCheckerDuplicate->shouldReceive('__invoke')
            ->andReturn(UserId::fromUuid(Uuid::uuid4()))
            ->byDefault();
    }

    private function getUserActive(bool $sendInvite = false): User
    {
        return $this->getUser(true, $sendInvite);
    }

    private function getUserInactive(bool $sendInvite = false): User
    {
        return $this->getUser(false, $sendInvite);
    }

    private function getUser(bool $active, bool $sendInvite): User
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $email = $faker->emailVo;
        $password = $faker->password;
        $role = Role::ROLE_USER();
        $firstName = Name::fromString($faker->firstName);
        $lastName = Name::fromString($faker->lastName);

        $user = User::addByAdmin(
            $userId,
            $email,
            $password,
            $role,
            $active,
            $firstName,
            $lastName,
            $sendInvite,
            $this->userUniquenessCheckerNone
        );
        $this->popRecordedEvent($user);

        return $user;
    }
}
