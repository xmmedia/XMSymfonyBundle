<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Form\User;

use Mockery;
use Symfony\Component\Security\Core\Security;
use Xm\SymfonyBundle\Entity\User;
use Xm\SymfonyBundle\Form\User\UserProfileType;
use Xm\SymfonyBundle\Model\User\Service\ChecksUniqueUsersEmail;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Tests\TypeTestCase;
use Xm\SymfonyBundle\Validator\Constraints\UniqueCurrentUsersEmailValidator;

class UserProfileTypeTest extends TypeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $faker = $this->faker();
        $userId = UserId::fromString($faker->uuid);

        $checker = Mockery::mock(ChecksUniqueUsersEmail::class);
        $checker->shouldReceive('__invoke')
            ->andReturn($userId);

        $user = Mockery::mock(User::class);
        $user->shouldReceive('userId')
            ->andReturn($userId);

        $security = Mockery::mock(Security::class);
        $security->shouldReceive('getUser')
            ->andReturn($user);

        $this->validatorContainer->set(
            UniqueCurrentUsersEmailValidator::class,
            new UniqueCurrentUsersEmailValidator($checker, $security)
        );
    }

    public function test()
    {
        $faker = $this->faker();

        $formData = [
            'email'     => $faker->email,
            'firstName' => $faker->name,
            'lastName'  => $faker->name,
        ];

        $form = $this->factory->create(UserProfileType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
    }
}
