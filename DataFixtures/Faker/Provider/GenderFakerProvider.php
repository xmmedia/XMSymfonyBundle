<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataFixtures\Faker\Provider;

use Faker;
use Xm\SymfonyBundle\Model\Gender;

/**
 * @codeCoverageIgnore
 */
class GenderFakerProvider extends Faker\Provider\Person
{
    public function gender(): string
    {
        $faker = Faker\Factory::create();

        return $faker->randomElement(Gender::getValues());
    }
}
