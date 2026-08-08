<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Faker;
use Xm\SymfonyBundle\DataFixtures\Faker\Provider;

trait UsesFaker
{
    private static Faker\Generator $faker;

    /**
     * Static so that it can also be used from data providers,
     * which must be static as of PHPUnit 10.
     *
     * @return Faker\Generator|Provider\AddressFakerProvider|Provider\DateFakerProvider|Provider\EmailFakerProvider|Provider\GenderFakerProvider|Provider\InternetFakerProvider|Provider\NameFakerProvider|Provider\PhoneNumberFakerProvider|Provider\StringFakerProvider|Provider\UuidFakerProvider
     */
    protected static function faker(): Faker\Generator
    {
        return !isset(self::$faker) ? self::makeFaker() : self::$faker;
    }

    private static function makeFaker(): Faker\Generator
    {
        $locales = ['en_CA', 'en_US'];

        self::$faker = Faker\Factory::create($locales[array_rand($locales)]);
        self::$faker->addProvider(new Provider\AddressFakerProvider(self::$faker));
        self::$faker->addProvider(new Provider\DateFakerProvider(self::$faker));
        self::$faker->addProvider(new Provider\EmailFakerProvider(self::$faker));
        self::$faker->addProvider(new Provider\GenderFakerProvider(self::$faker));
        self::$faker->addProvider(new Provider\InternetFakerProvider(self::$faker));
        self::$faker->addProvider(new Provider\NameFakerProvider(self::$faker));
        self::$faker->addProvider(new Provider\PhoneNumberFakerProvider(self::$faker));
        self::$faker->addProvider(new Provider\StringFakerProvider(self::$faker));
        self::$faker->addProvider(new Provider\UuidFakerProvider(self::$faker));

        return self::$faker;
    }
}
