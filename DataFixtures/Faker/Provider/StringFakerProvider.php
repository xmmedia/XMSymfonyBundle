<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataFixtures\Faker\Provider;

use Faker;

/**
 * @codeCoverageIgnore
 */
class StringFakerProvider extends Faker\Provider\Base
{
    public function string(int $length): string
    {
        $faker = Faker\Factory::create();

        return $faker->asciify(str_repeat('*', $length));
    }
}
