<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\DataProvider;

use PHPUnit\Framework\TestCase;
use Xm\SymfonyBundle\DataProvider\ProvinceProvider;
use Xm\SymfonyBundle\Exception\InvalidProvince;
use Xm\SymfonyBundle\Model\Country;

class ProvinceProviderTest extends TestCase
{
    public function testAllByCountry(): void
    {
        $all = ProvinceProvider::all();

        $this->assertCount(2, $all);
        $this->assertCount(13, $all['CA']);
        $this->assertCount(51, $all['US']);
    }

    public function testAllAll(): void
    {
        $all = ProvinceProvider::all(false);

        $this->assertCount(64, $all);
    }

    public function testAbbreviationsByCountry(): void
    {
        $abbreviations = ProvinceProvider::abbreviations();

        $this->assertCount(2, $abbreviations);
        $this->assertCount(13, $abbreviations['CA']);
        $this->assertCount(51, $abbreviations['US']);
    }

    public function testAbbreviationsAll(): void
    {
        $abbreviations = ProvinceProvider::abbreviations(false);

        $this->assertCount(64, $abbreviations);
    }

    public function testName(): void
    {
        $this->assertEquals('Alberta', ProvinceProvider::name('AB'));
    }

    public function testCountry(): void
    {
        $this->assertEquals(
            Country::fromString('CA'),
            ProvinceProvider::country('AB')
        );
    }

    public function testCountryUnknownProvince(): void
    {
        $this->expectException(InvalidProvince::class);

        ProvinceProvider::country('88');
    }
}
