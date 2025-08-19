<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataFixtures\Faker\Provider;

use Faker;

/**
 * @codeCoverageIgnore
 */
class NameFakerProvider extends Faker\Provider\Person
{
    public function name(null|string $gender = null): string
    {
        $faker = Faker\Factory::create();

        return trim(substr($faker->name($gender), 0, 25));
    }
}
