<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Faker;
use Xm\SymfonyBundle\DataFixtures\Faker\Provider;

trait UsesFaker
{
    private Faker\Generator $faker;

    /**
     * @return Faker\Generator|Provider\AddressFakerProvider|Provider\DateFakerProvider|Provider\EmailFakerProvider|Provider\GenderFakerProvider|Provider\InternetFakerProvider|Provider\NameFakerProvider|Provider\PhoneNumberFakerProvider|Provider\StringFakerProvider|Provider\UuidFakerProvider
     */
    protected function faker(): Faker\Generator
    {
        return !isset($this->faker) ? $this->makeFaker() : $this->faker;
    }

    private function makeFaker(): Faker\Generator
    {
        $locales = ['en_CA', 'en_US'];

        $this->faker = Faker\Factory::create($locales[array_rand($locales)]);
        $this->faker->addProvider(new Provider\AddressFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\DateFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\EmailFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\GenderFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\InternetFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\NameFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\PhoneNumberFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\StringFakerProvider($this->faker));
        $this->faker->addProvider(new Provider\UuidFakerProvider($this->faker));

        return $this->faker;
    }
}
