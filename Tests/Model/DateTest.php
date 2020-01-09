<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use Xm\SymfonyBundle\Model\Date;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeVo;
use Xm\SymfonyBundle\Util\Json;

class DateTest extends BaseTestCase
{
    /**
     * @dataProvider dateStringProvider
     */
    public function testFromStringAndToString(
        string $string,
        string $expected,
        string $timezone
    ): void {
        $date = Date::fromString($string);

        $this->assertEquals($expected, $date->format(\DateTime::ISO8601));
        $this->assertEquals($timezone, $date->date()->timezone->getName());
    }

    public function dateStringProvider(): \Generator
    {
        $faker = $this->faker();
        $max = '+5 years';

        $str = $faker->iso8601($max);
        yield [$str, $str, '+00:00'];

        $str = $faker->date('Y-m-d', $max);
        yield [$str, $str.'T00:00:00+0000', 'UTC'];
    }

    public function testNow(): void
    {
        $now = new \DateTimeImmutable();
        $date = Date::now();

        $this->assertEquals(
            $now->format(\DateTime::ISO8601),
            $date->date()->format(\DateTime::ISO8601)
        );
        $this->assertEquals('UTC', $date->date()->timezone->getName());
    }

    public function testNowUtc(): void
    {
        $now = new \DateTimeImmutable(
            'now',
            new \DateTimeZone('America/Edmonton')
        );
        $date = Date::now('America/Edmonton');

        $this->assertEquals(
            $now->format(\DateTime::ISO8601),
            $date->date()->format(\DateTime::ISO8601)
        );
        $this->assertEquals('America/Edmonton', $date->date()->timezone->getName());
    }

    /**
     * @dataProvider dateTimeProvider
     */
    public function testFromDateTime(\DateTimeInterface $dateTime): void
    {
        $date = Date::fromDateTime($dateTime);

        $this->assertEquals(
            $dateTime->format(\DateTime::ISO8601),
            $date->date()->format(\DateTime::ISO8601)
        );
        $this->assertEquals('UTC', $date->date()->timezone->getName());
    }

    public function dateTimeProvider(): \Generator
    {
        yield [new \DateTime()];
        yield [new \DateTimeImmutable()];
    }

    public function testFormat(): void
    {
        $dateString = '2000-01-01';
        $date = Date::fromString($dateString);

        $this->assertEquals($dateString, $date->format('Y-m-d'));
    }

    public function testToString(): void
    {
        $dateString = '2000-01-01';
        $date = Date::fromString($dateString);

        $this->assertEquals($dateString, $date->toString());
        $this->assertEquals($dateString, (string) $date);
    }

    public function jsonProvider(): \Generator
    {
        yield [Date::fromString('2000-01-01'), '"2000-01-01T00:00:00.000000Z"'];

        $date = Date::now();
        yield [$date, '"'.$date->format('Y-m-d\TH:i:s.u\Z').'"'];
    }

    /**
     * @dataProvider sameValueAsProvider
     */
    public function testSameValueAs(string $date1, string $date2, bool $expected): void
    {
        $date1 = Date::fromString($date1);
        $date2 = Date::fromString($date2);

        $this->assertEquals($expected, $date1->sameValueAs($date2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $date = Date::fromString('2019-01-01');

        $this->assertFalse($date->sameValueAs(FakeVo::create()));
    }

    public function sameValueAsProvider(): \Generator
    {
        yield ['2000-01-01', '2000-01-01', true];
        yield ['2000-01-01 00:00:00', '2000-01-01 00:00:00', true];
        yield ['2000-01-01 00:00:00.0', '2000-01-01 00:00:00.0', true];
        // start microseconds which are ignored in same as comparison
        yield ['2000-01-01 00:00:00.0000', '2000-01-01 00:00:00.0001', true];
        yield ['2000-01-01 00:00:00.00000', '2000-01-01 00:00:00.00001', true];
        yield ['2000-01-01 00:00:00.000000', '2000-01-01 00:00:00.000001', true];

        yield ['2000-01-01', '2000-01-02', false];
        yield ['2000-01-01 00:00:00', '2000-01-01 10:00:00', false];
        yield ['2000-01-01 00:00:00', '2000-01-01 00:01:00', false];
        yield ['2000-01-01 00:00:00', '2000-01-01 00:00:01', false];
        // 1000 is the max milliseconds
        yield ['2000-01-01 00:00:00.0', '2000-01-01 00:00:00.1', false];
        yield ['2000-01-01 00:00:00.00', '2000-01-01 00:00:00.01', false];
        yield ['2000-01-01 00:00:00.000', '2000-01-01 00:00:00.001', false];
    }

    public function testInvalid(): void
    {
        $this->expectException(\Exception::class);

        Date::fromString('asdf');
    }
}
