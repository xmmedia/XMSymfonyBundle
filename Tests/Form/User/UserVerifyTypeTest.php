<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Form\User;

use Xm\SymfonyBundle\Form\User\UserVerifyType;
use Xm\SymfonyBundle\Tests\TypeTestCase;

class UserVerifyTypeTest extends TypeTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $newPassword = $faker->password(12, 250);

        $formData = [
            'token'       => $faker->password,
            'password'    => [
                'first'  => $newPassword,
                'second' => $newPassword,
            ],
        ];

        $form = $this->factory->create(UserVerifyType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
    }
}
