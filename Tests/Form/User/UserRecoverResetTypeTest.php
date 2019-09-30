<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Form\User;

use Xm\SymfonyBundle\Form\User\UserRecoverResetType;
use Xm\SymfonyBundle\Tests\TypeTestCase;

class UserRecoverResetTypeTest extends TypeTestCase
{
    public function test()
    {
        $faker = $this->faker();

        $newPassword = $faker->password(12, 250);

        $formData = [
            'token'       => $faker->password,
            'newPassword' => [
                'first'  => $newPassword,
                'second' => $newPassword,
            ],
        ];

        $form = $this->factory->create(UserRecoverResetType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
    }
}
