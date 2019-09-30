<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Form\User;

use Mockery;
use Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator;
use Xm\SymfonyBundle\Form\User\UserChangePasswordType;
use Xm\SymfonyBundle\Tests\TypeTestCase;

class UserChangePasswordTypeTest extends TypeTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $validator = Mockery::mock(UserPasswordValidator::class);
        $validator->shouldReceive('initialize');
        $validator->shouldReceive('validate');

        $this->validatorContainer->set(
            'security.validator.user_password',
            $validator
        );
    }

    public function test()
    {
        $faker = $this->faker();

        $newPassword = $faker->password(12, 250);

        $formData = [
            'currentPassword' => $faker->password,
            'newPassword'     => [
                'first'  => $newPassword,
                'second' => $newPassword,
            ],
        ];

        $form = $this->factory->create(UserChangePasswordType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
    }
}
