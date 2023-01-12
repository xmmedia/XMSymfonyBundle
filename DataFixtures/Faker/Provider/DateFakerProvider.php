<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataFixtures\Faker\Provider;

use Faker;
use Xm\SymfonyBundle\Model\Date;

/**
 * @codeCoverageIgnore
 */
class DateFakerProvider extends Faker\Provider\Base
{
    public function dateVoBetween(
        string $min = '-30 years',
        string $max = 'now',
    ): Date {
        $faker = Faker\Factory::create('en_CA');

        return Date::fromDateTime($faker->dateTimeBetween($min, $max));
    }
}
