<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Validator\Constraints;

use Mockery;
use Symfony\Component\Validator\Context\ExecutionContext;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilder;
use Xm\SymfonyBundle\Entity\User;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\Service\ChecksUniqueUsersEmail;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Model\User\UserIdInterface;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Validator\Constraints\UniqueCurrentUsersEmail;
use Xm\SymfonyBundle\Validator\Constraints\UniqueExistingUserEmailValidator;

class UniqueExistingUserEmailValidatorTest extends BaseTestCase
{
    public function testUnique(): void
    {
        $faker = $this->faker();

        $uniqueChecker = new UniqueExistingUserEmailValidatorTestUniquenessCheckerNone();

        $user = Mockery::mock(User::class);
        $user->shouldNotReceive('userId')
            ->andReturn($faker->userId);

        $constraint = Mockery::mock(UniqueCurrentUsersEmail::class);

        $validator = new UniqueExistingUserEmailValidator($uniqueChecker);

        $validator->validate(
            [
                'userId' => $faker->uuid,
                'email'  => $faker->email,
            ],
            $constraint
        );
    }

    public function testSameUser(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $uniqueChecker = new UniqueExistingUserEmailValidatorTestUniquenessCheckerDuplicate($userId);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($userId);

        $constraint = Mockery::mock(UniqueCurrentUsersEmail::class);

        $validator = new UniqueExistingUserEmailValidator($uniqueChecker);

        $validator->validate(
            [
                'email'  => $faker->email,
                'userId' => $userId->toString(),
            ],
            $constraint
        );
    }

    public function testNotUnique(): void
    {
        $faker = $this->faker();

        $uniqueChecker = new UniqueExistingUserEmailValidatorTestUniquenessCheckerDuplicate(
            $faker->userId
        );

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($faker->userId);

        $constraint = Mockery::mock(UniqueCurrentUsersEmail::class);

        $validator = new UniqueExistingUserEmailValidator($uniqueChecker);

        $builder = Mockery::mock(ConstraintViolationBuilder::class);
        $builder->shouldReceive('atPath')
            ->once()
            ->with('[email]')
            ->andReturnSelf();
        $builder->shouldReceive('addViolation')
            ->once();

        $context = Mockery::mock(ExecutionContext::class);
        $context->shouldReceive('buildViolation')
            ->once()
            ->with($constraint->message)
            ->andReturn($builder);

        $validator->initialize($context);

        $validator->validate(
            [
                'email'  => $faker->email,
                'userId' => $faker->uuid,
            ],
            $constraint
        );
    }
}

class UniqueExistingUserEmailValidatorTestUniquenessCheckerNone implements ChecksUniqueUsersEmail
{
    public function __invoke(Email $email): ?UserIdInterface
    {
        return null;
    }
}

class UniqueExistingUserEmailValidatorTestUniquenessCheckerDuplicate implements ChecksUniqueUsersEmail
{
    /** @var UserId */
    private $userId;

    public function __construct(UserIdInterface $userId)
    {
        $this->userId = $userId;
    }

    public function __invoke(Email $email): ?UserIdInterface
    {
        return $this->userId;
    }
}
