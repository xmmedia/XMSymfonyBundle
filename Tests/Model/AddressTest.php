<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use Xm\SymfonyBundle\Exception\InvalidAddress;
use Xm\SymfonyBundle\Model\Address;
use Xm\SymfonyBundle\Model\Country;
use Xm\SymfonyBundle\Model\PostalCode;
use Xm\SymfonyBundle\Model\Province;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeVo;

class AddressTest extends BaseTestCase
{
    /**
     * @dataProvider addressStringProvider
     */
    public function testFromStrings(
        string $line1,
        ?string $line2,
        string $city,
        string $province,
        string $postalCode,
        string $country,
        string $expectedPostalCode,
    ): void {
        $address = Address::fromStrings(
            $line1,
            $line2,
            $city,
            $province,
            $postalCode,
            $country,
        );

        $this->assertEquals($line1, $address->line1());
        $this->assertEquals($line2, $address->line2());
        $this->assertEquals($city, $address->city());
        $this->assertEquals($province, $address->province()->toString());
        $this->assertEquals($expectedPostalCode, $address->postalCode()->toString());
        $this->assertEquals($country, $address->country()->toString());
    }

    public function addressStringProvider(): \Generator
    {
        $faker = $this->faker();

        $postalCode = $this->postalCode();
        yield [
            $faker->streetAddress(),
            $faker->streetAddress(),
            $faker->city(),
            $faker->stateAbbr(),
            $postalCode,
            $faker->randomElement(['CA', 'US']),
            PostalCode::format($postalCode),
        ];

        $postalCode = $this->postalCode();
        yield [
            $faker->streetAddress(),
            null,
            $faker->city(),
            $faker->stateAbbr(),
            $postalCode,
            $faker->randomElement(['CA', 'US']),
            PostalCode::format($postalCode),
        ];

        $postalCode = $this->postalCode();
        yield [
            $faker->streetAddress(),
            '', // empty string is changed to null
            $faker->city(),
            $faker->stateAbbr(),
            $postalCode,
            $faker->randomElement(['CA', 'US']),
            PostalCode::format($postalCode),
        ];
    }

    /**
     * @dataProvider addressArrayProvider
     */
    public function testFromArray(array $data, string $expectedPostalCode): void
    {
        $address = Address::fromArray($data);

        $this->assertEquals($data['line1'], $address->line1());
        $this->assertEquals($data['line2'], $address->line2());
        $this->assertEquals($data['city'], $address->city());
        $this->assertEquals($data['province'], $address->province()->toString());
        $this->assertEquals($expectedPostalCode, $address->postalCode()->toString());
        $this->assertEquals($data['country'], $address->country()->toString());

        $data['postalCode'] = $expectedPostalCode;
        $this->assertEquals($data, $address->toArray());
    }

    public function addressArrayProvider(): \Generator
    {
        $faker = $this->faker();

        $postalCode = $this->postalCode();
        yield [
            [
                'line1'      => $faker->streetAddress(),
                'line2'      => $faker->streetAddress(),
                'city'       => $faker->city(),
                'province'   => $faker->stateAbbr(),
                'postalCode' => $postalCode,
                'country'    => $faker->randomElement(['CA', 'US']),
            ],
            PostalCode::format($postalCode),
        ];

        $postalCode = $this->postalCode();
        yield [
            [
                'line1'      => $faker->streetAddress(),
                'line2'      => null,
                'city'       => $faker->city(),
                'province'   => $faker->stateAbbr(),
                'postalCode' => $postalCode,
                'country'    => $faker->randomElement(['CA', 'US']),
            ],
            PostalCode::format($postalCode),
        ];

        $postalCode = $this->postalCode();
        yield [
            [
                'line1'      => $faker->streetAddress(),
                'line2'      => '', // empty string is changed to null
                'city'       => $faker->city(),
                'province'   => $faker->stateAbbr(),
                'postalCode' => $postalCode,
                'country'    => $faker->randomElement(['CA', 'US']),
            ],
            PostalCode::format($postalCode),
        ];

        $postalCode = $this->postalCode();
        yield [
            [
                'line1'      => $faker->streetAddress(),
                'line2'      => $faker->streetAddress(),
                'city'       => $faker->city(),
                'province'   => Province::fromString($faker->stateAbbr()),
                'postalCode' => PostalCode::fromString($postalCode),
                'country'    => Country::fromString(
                    $faker->randomElement(['CA', 'US']),
                ),
            ],
            PostalCode::format($postalCode),
        ];
    }

    /**
     * Only tests exceptions through directly in Address VO, not Province, etc.
     *
     * @dataProvider addressInvalidProvider
     */
    public function testInvalid(
        string $line1,
        ?string $line2,
        string $city,
        string $province,
        string $postalCode,
        string $country,
        string $exception,
        string $message,
    ): void {
        $this->expectException($exception);
        $this->expectExceptionMessage($message);

        Address::fromStrings(
            $line1,
            $line2,
            $city,
            $province,
            $postalCode,
            $country,
        );
    }

    public function addressInvalidProvider(): \Generator
    {
        $faker = $this->faker();

        yield [
            'a',
            '',
            'Calgary',
            'AB',
            'T3L 2H9',
            $faker->randomElement(['CA', 'US']),
            InvalidAddress::class,
            'The address line 1',
        ];

        yield [
            $faker->string(101),
            '',
            'Calgary',
            'AB',
            'T3L 2H9',
            $faker->randomElement(['CA', 'US']),
            InvalidAddress::class,
            'The address line 1',
        ];

        yield [
            $faker->streetAddress(),
            'a',
            'Calgary',
            'AB',
            'T3L 2H9',
            $faker->randomElement(['CA', 'US']),
            InvalidAddress::class,
            'The address line 2',
        ];

        yield [
            $faker->streetAddress(),
            $faker->string(101),
            'Calgary',
            'AB',
            'T3L 2H9',
            $faker->randomElement(['CA', 'US']),
            InvalidAddress::class,
            'The address line 2',
        ];

        yield [
            $faker->streetAddress(),
            $faker->streetAddress(),
            'a',
            'AB',
            'T3L 2H9',
            $faker->randomElement(['CA', 'US']),
            InvalidAddress::class,
            'The address city',
        ];

        yield [
            $faker->streetAddress(),
            $faker->streetAddress(),
            $faker->string(101),
            'AB',
            'T3L 2H9',
            $faker->randomElement(['CA', 'US']),
            InvalidAddress::class,
            'The address city',
        ];
    }

    public function testToString(): void
    {
        $faker = $this->faker();

        $address = $faker->addressVo();

        $string = $address->line1()."\n".
            $address->line2()."\n".
            $address->city().', '.
            $address->province()->toString().'  '.
            $address->postalCode()->toString()."\n".
            $address->country()->name();

        $this->assertSame($string, $address->toString());
        $this->assertSame($string, (string) $address);
    }

    public function testToStringNoLine2(): void
    {
        $faker = $this->faker();

        $address = Address::fromStrings(
            $faker->streetAddress(),
            '',
            $faker->city(),
            $faker->stateAbbr(),
            $this->postalCode(),
            $faker->randomElement(['CA', 'US']),
        );
        $string = $address->line1()."\n".
            $address->city().', '.
            $address->province()->toString().'  '.
            $address->postalCode()->toString()."\n".
            $address->country()->name();

        $this->assertSame($string, $address->toString());
        $this->assertSame($string, (string) $address);
    }

    public function testToStringHtml(): void
    {
        $faker = $this->faker();

        $address = Address::fromStrings(
            $faker->streetAddress(),
            $faker->streetAddress(),
            $faker->city(),
            $faker->stateAbbr(),
            $this->postalCode(),
            $faker->randomElement(['CA', 'US']),
        );
        $string = $address->line1().'<br>'.
            $address->line2().'<br>'.
            $address->city().', '.
            $address->province()->toString().' &nbsp;'.
            $address->postalCode()->toString().'<br>'.
            $address->country()->name();

        $this->assertSame($string, $address->toString(true));
    }

    public function testToStringHtmlNoLine2(): void
    {
        $faker = $this->faker();

        $address = Address::fromStrings(
            $faker->streetAddress(),
            '',
            $faker->city(),
            $faker->stateAbbr(),
            $this->postalCode(),
            $faker->randomElement(['CA', 'US']),
        );
        $string = $address->line1().'<br>'.
            $address->city().', '.
            $address->province()->toString().' &nbsp;'.
            $address->postalCode()->toString().'<br>'.
            $address->country()->name();

        $this->assertSame($string, $address->toString(true));
    }

    public function testToStringExcludeCountry(): void
    {
        $faker = $this->faker();

        $address = Address::fromStrings(
            $faker->streetAddress(),
            '',
            $faker->city(),
            $faker->stateAbbr(),
            $this->postalCode(),
            $faker->randomElement(['CA', 'US']),
        );
        $string = $address->line1()."\n".
            $address->city().', '.
            $address->province()->toString().'  '.
            $address->postalCode()->toString();

        $this->assertSame($string, $address->toString(false, false));
    }

    public function testToStringHtmlExcludeCountry(): void
    {
        $faker = $this->faker();

        $address = Address::fromStrings(
            $faker->streetAddress(),
            $faker->streetAddress(),
            $faker->city(),
            $faker->stateAbbr(),
            $this->postalCode(),
            $faker->randomElement(['CA', 'US']),
        );
        $string = $address->line1().'<br>'.
            $address->line2().'<br>'.
            $address->city().', '.
            $address->province()->toString().' &nbsp;'.
            $address->postalCode()->toString();

        $this->assertSame($string, $address->toString(true, false));
    }

    /**
     * @dataProvider addressArrayProvider
     */
    public function testSameAs(array $data): void
    {
        $address1 = Address::fromArray($data);
        $address2 = Address::fromArray($data);

        $this->assertTrue($address1->sameValueAs($address2));
    }

    /**
     * @dataProvider addressArrayProvider
     */
    public function testSameAsFalse(array $data): void
    {
        $address1 = Address::fromArray($data);
        $address2 = Address::fromArray(['line1' => 'diff'] + $data);

        $this->assertFalse($address1->sameValueAs($address2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $faker = $this->faker();

        $address = $faker->addressVo();

        $this->assertFalse($address->sameValueAs(FakeVo::create()));
    }

    private function postalCode(): string
    {
        return str_replace('-', ' ', $this->faker()->postcode());
    }
}
