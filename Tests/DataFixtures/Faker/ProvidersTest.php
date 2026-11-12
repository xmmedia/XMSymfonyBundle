<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\DataFixtures\Faker;

use Faker;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Xm\SymfonyBundle\DataFixtures\Faker\Provider;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\Gender;

/**
 * The providers are added after the locale's, so any method they inherit
 * replaces the locale's version for the whole generator.
 */
class ProvidersTest extends TestCase
{
    private const int SAMPLES = 100;

    public static function localeProvider(): \Generator
    {
        yield ['en_CA'];
        yield ['en_US'];
    }

    #[DataProvider('localeProvider')]
    public function testLocaleNamesNotReplaced(string $locale): void
    {
        $faker = $this->makeFaker($locale);

        $this->assertGreaterThan(10, $this->distinct(static fn (): string => $faker->firstName()));
        $this->assertGreaterThan(10, $this->distinct(static fn (): string => $faker->lastName()));
    }

    #[DataProvider('localeProvider')]
    public function testEmailsVaried(string $locale): void
    {
        $faker = $this->makeFaker($locale);

        $this->assertGreaterThan(self::SAMPLES * 0.9, $this->distinct(static fn (): string => $faker->email()));
        $this->assertInstanceOf(Email::class, $faker->emailVo());
    }

    public function testPasswordLength(): void
    {
        $this->assertGreaterThanOrEqual(12, mb_strlen($this->makeFaker('en_US')->password()));
    }

    public function testGender(): void
    {
        $this->assertContains($this->makeFaker('en_US')->gender(), Gender::getValues());
    }

    private function makeFaker(string $locale): Faker\Generator
    {
        $faker = Faker\Factory::create($locale);
        $faker->addProvider(new Provider\EmailFakerProvider($faker));
        $faker->addProvider(new Provider\GenderFakerProvider($faker));
        $faker->addProvider(new Provider\InternetFakerProvider($faker));
        $faker->addProvider(new Provider\NameFakerProvider($faker));

        return $faker;
    }

    private function distinct(callable $generate): int
    {
        $values = [];
        for ($i = 0; $i < self::SAMPLES; ++$i) {
            $values[] = $generate();
        }

        return \count(array_unique($values));
    }
}
