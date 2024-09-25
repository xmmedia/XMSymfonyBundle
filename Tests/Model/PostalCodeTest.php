<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Xm\SymfonyBundle\Exception\InvalidPostalCode;
use Xm\SymfonyBundle\Model\PostalCode;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeVo;

class PostalCodeTest extends BaseTestCase
{
    /**
     * @dataProvider postalCodeProvider
     */
    public function testFromString(string $code, string $expected): void
    {
        $province = PostalCode::fromString($code);

        $this->assertEquals($expected, $province->toString());
        $this->assertEquals($expected, (string) $province);
    }

    public function postalCodeProvider(): \Generator
    {
        yield ['T9D 8K2', 'T9D 8K2'];
        yield ['T9D8K2', 'T9D 8K2'];
        yield ['T9D-8K2', 'T9D 8K2'];

        yield ['50301', '50301'];

        yield ['20521-9000', '205219000'];
    }

    /**
     * @dataProvider invalidProvider
     */
    public function testInvalid(?string $value): void
    {
        $this->expectException(InvalidPostalCode::class);

        PostalCode::fromString($value);
    }

    public function invalidProvider(): \Generator
    {
        yield ['2332'];
        yield [''];
        yield ['32333-323333'];
    }

    public function testSameValueAs(): void
    {
        $province1 = PostalCode::fromString('T9D 8K2');
        $province2 = PostalCode::fromString('T9D 8K2');

        $this->assertTrue($province1->sameValueAs($province2));
    }

    public function testSameValueAsFalse(): void
    {
        $province1 = PostalCode::fromString('T9D 8K2');
        $province2 = PostalCode::fromString('T9D 2H8');

        $this->assertFalse($province1->sameValueAs($province2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $province = PostalCode::fromString('T9D 8K2');

        $this->assertFalse($province->sameValueAs(FakeVo::create()));
    }
}
