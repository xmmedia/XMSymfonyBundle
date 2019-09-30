<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Form\User;

use Xm\SymfonyBundle\Form\User\UserRecoverInitiateType;
use Xm\SymfonyBundle\Tests\TypeTestCase;

class UserRecoverInitiateTypeTest extends TypeTestCase
{
    public function test()
    {
        $faker = $this->faker();

        $formData = [
            'email' => $faker->email,
        ];

        $form = $this->factory->create(UserRecoverInitiateType::class)
            ->submit($formData);

        $this->assertFormIsValid($form);
        $this->hasAllFormFields($form, $formData);
    }
}
