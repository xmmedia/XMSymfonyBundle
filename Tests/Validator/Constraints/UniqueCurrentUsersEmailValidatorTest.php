<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Validator\Constraints;

use Mockery;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;
use Xm\SymfonyBundle\Entity\User;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\Service\ChecksUniqueUsersEmail;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Model\User\UserIdInterface;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Validator\Constraints\UniqueCurrentUsersEmail;
use Xm\SymfonyBundle\Validator\Constraints\UniqueCurrentUsersEmailValidator;

class UniqueCurrentUsersEmailValidatorTest extends BaseTestCase
{
    public function testUnique(): void
    {
        $faker = $this->faker();

        $uniqueChecker = new UniqueCurrentUsersEmailValidatorTestUniquenessCheckerNone();

        $user = Mockery::mock(User::class);
        $user->shouldNotReceive('userId')
            ->andReturn($faker->userId);

        $constraint = Mockery::mock(UniqueCurrentUsersEmail::class);

        $validator = new UniqueCurrentUsersEmailValidator(
            $uniqueChecker,
            $this->createSecurity($user)
        );

        $validator->validate($faker->email, $constraint);
    }

    public function testSameUser(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $uniqueChecker = new UniqueCurrentUsersEmailValidatorTestUniquenessCheckerDuplicate($userId);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($userId);

        $constraint = Mockery::mock(UniqueCurrentUsersEmail::class);

        $validator = new UniqueCurrentUsersEmailValidator(
            $uniqueChecker,
            $this->createSecurity($user)
        );

        $validator->validate($faker->email, $constraint);
    }

    public function testNotUnique(): void
    {
        $faker = $this->faker();

        $uniqueChecker = new UniqueCurrentUsersEmailValidatorTestUniquenessCheckerDuplicate($faker->userId);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);

        $constraint = Mockery::mock(UniqueCurrentUsersEmail::class);

        $validator = new UniqueCurrentUsersEmailValidator(
            $uniqueChecker,
            $this->createSecurity($user)
        );

        $builder = Mockery::mock(ConstraintViolationBuilder::class);
        $builder->shouldReceive('addViolation')
            ->once();

        $context = Mockery::mock(ExecutionContext::class);
        $context->shouldReceive('buildViolation')
            ->once()
            ->with($constraint->message)
            ->andReturn($builder);

        $validator->initialize($context);

        $validator->validate($faker->email, $constraint);
    }

    /**
     * $user: false = no token storage within container, null = no user.
     *
     * @param UserInterface|bool|null $user
     */
    private function createSecurity($user): Security
    {
        $tokenStorage = Mockery::mock(TokenStorageInterface::class);

        if (false !== $user) {
            $token = Mockery::mock(TokenInterface::class);
            $token->shouldReceive('getUser')
                ->andReturn($user)
            ;

            $tokenStorage->shouldReceive('getToken')
                ->andReturn($token)
            ;
        }

        $container = $this->createContainer('security.token_storage', $tokenStorage);

        return new Security($container);
    }

    private function createContainer($serviceId, $serviceObject): ContainerInterface
    {
        $container = Mockery::mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->with($serviceId)
            ->andReturn($serviceObject);

        return $container;
    }
}

class UniqueCurrentUsersEmailValidatorTestUniquenessCheckerNone implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserIdInterface
    {
        return null;
    }
}

class UniqueCurrentUsersEmailValidatorTestUniquenessCheckerDuplicate implements ChecksUniqueUsersEmail
{
    /** @var UserId */
    private $userId;

    public function __construct(UserId $userId)
    {
        $this->userId = $userId;
    }

    public function __invoke(Email $email): ?UserIdInterface
    {
        return $this->userId;
    }
}
