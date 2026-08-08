<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Xm\SymfonyBundle\Exception\InvalidCountry;
use Xm\SymfonyBundle\Model\Country;
use Xm\SymfonyBundle\Tests\FakeVo;

class CountryTest extends TestCase
{
    /**
     * @dataProvider countryProvider
     */
    public function testFromString(
        string $abbreviation,
        string $expected,
        string $name,
    ): void {
        $country = Country::fromString($abbreviation);

        $this->assertEquals($expected, $country->abbreviation());
        $this->assertEquals($name, $country->name());
        $this->assertEquals($expected, $country->toString());
        $this->assertEquals($expected, (string) $country);
    }

    public static function countryProvider(): \Generator
    {
        yield ['CA', 'CA', 'Canada'];
        yield ['ca', 'CA', 'Canada'];

        yield ['US', 'US', 'United States'];
        yield ['us', 'US', 'United States'];
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid(?string $value): void
    {
        $this->expectException(InvalidCountry::class);

        Country::fromString($value);
    }

    public static function invalidProvider(): \Generator
    {
        yield ['UK'];
        yield [''];
        yield ['A'];
    }

    public function testSameValueAs(): void
    {
        $country1 = Country::fromString('CA');
        $country2 = Country::fromString('CA');

        $this->assertTrue($country1->sameValueAs($country2));
    }

    public function testSameValueAsFalse(): void
    {
        $country1 = Country::fromString('CA');
        $country2 = Country::fromString('US');

        $this->assertFalse($country1->sameValueAs($country2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $country = Country::fromString('CA');

        $this->assertFalse($country->sameValueAs(FakeVo::create()));
    }
}
