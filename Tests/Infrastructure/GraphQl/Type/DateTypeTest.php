<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Type;

use Xm\SymfonyBundle\Infrastructure\GraphQl\Type\DateType;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class DateTypeTest extends BaseTestCase
{
    public function testParseValue(): void
    {
        $date = $this->faker()->date('Y-m-d');

        $type = new DateType();
        $result = $type->parseValue($date);

        $this->assertSame($date, $result->format('Y-m-d'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nonStringProvider')]
    public function testParseValueNonString(mixed $value): void
    {
        $this->expectException(\Overblog\GraphQLBundle\Error\UserError::class);
        $this->expectExceptionMessageIsOrContains('The date is not in the format of YYYY-MM-DD.');

        (new DateType())->parseValue($value);
    }

    public static function nonStringProvider(): \Generator
    {
        yield 'int'    => [20230115];
        yield 'float'  => [1.5];
        yield 'true'   => [true];
        yield 'false'  => [false];
        yield 'array'  => [['2023-01-15']];
        yield 'object' => [new \stdClass()];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('emptyProvider')]
    public function testParseValueEmpty(?string $empty): void
    {
        $type = new DateType();
        $this->assertNull($type->parseValue($empty));
    }

    public static function emptyProvider(): array
    {
        return [
            [null],
            [''],
            [' '],
        ];
    }

    /**
     * Nearly impossible to test that PHP can't prase the date
     * because the regex ensures the format is correct before parsing.
     */
    public function testParseValueInvalidFormat(): void
    {
        $type = new DateType();

        $this->expectException(\Overblog\GraphQLBundle\Error\UserError::class);
        $this->expectExceptionMessageIsOrContains('The date is not in the format of YYYY-MM-DD. Received: "2023-13-1"');

        $type->parseValue('2023-13-1');
    }

    public function testSerializeWithDateTimeInterface(): void
    {
        $type = new DateType();
        $date = new \DateTimeImmutable('2023-05-15');

        $result = $type->serialize($date);

        $this->assertSame('2023-05-15', $result);
    }

    public function testSerializeWithDateTime(): void
    {
        $type = new DateType();
        $date = new \DateTime('2024-12-25');

        $result = $type->serialize($date);

        $this->assertSame('2024-12-25', $result);
    }

    public function testSerializeWithString(): void
    {
        $type = new DateType();

        $result = $type->serialize('2023-08-10');

        $this->assertSame('2023-08-10', $result);
    }

    public function testSerializeWithNull(): void
    {
        $type = new DateType();

        $result = $type->serialize(null);

        $this->assertNull($result);
    }

    public function testParseLiteralWithStringValueNode(): void
    {
        $type = new DateType();

        $node = new \GraphQL\Language\AST\StringValueNode([]);
        $node->value = '2023-06-20';

        $result = $type->parseLiteral($node);

        $this->assertInstanceOf(\DateTimeImmutable::class, $result);
        $this->assertSame('2023-06-20', $result->format('Y-m-d'));
    }

    public function testParseLiteralWithNonStringValueNode(): void
    {
        $type = new DateType();

        $node = new \GraphQL\Language\AST\IntValueNode([]);
        $node->value = '123';

        $result = $type->parseLiteral($node);

        $this->assertNull($result);
    }

    public function testParseLiteralWithVariables(): void
    {
        $type = new DateType();

        $node = new \GraphQL\Language\AST\StringValueNode([]);
        $node->value = '2023-09-30';

        $result = $type->parseLiteral($node, ['var' => 'value']);

        $this->assertInstanceOf(\DateTimeImmutable::class, $result);
        $this->assertSame('2023-09-30', $result->format('Y-m-d'));
    }

    public function testGetAliases(): void
    {
        $result = DateType::getAliases();

        $this->assertSame(['Date'], $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidDateFormatProvider')]
    public function testParseValueWithInvalidFormats(string $invalidDate): void
    {
        $type = new DateType();

        $this->expectException(\Overblog\GraphQLBundle\Error\UserError::class);

        $type->parseValue($invalidDate);
    }

    public static function invalidDateFormatProvider(): array
    {
        return [
            'invalid month'          => ['2023-13-01'],
            'month 00'               => ['2023-00-15'],
            'invalid day'            => ['2023-01-32'],
            'day 00'                 => ['2023-01-00'],
            'wrong format'           => ['01/15/2023'],
            'day first'              => ['15-01-2023'],
            'invalid separator'      => ['2023.01.15'],
            'slashes'                => ['2023/01/15'],
            'no separators'          => ['20230115'],
            'missing leading zero'   => ['2023-1-5'],
            'missing day zero'       => ['2023-01-5'],
            'two digit year'         => ['23-01-15'],
            'five digit year'        => ['12023-01-15'],
            'negative year'          => ['-2023-01-15'],
            'with time'              => ['2023-01-15 12:00:00'],
            'ISO 8601 with time'     => ['2023-01-15T12:00:00Z'],
            'with time zone'         => ['2023-01-15+00:00'],
            'trailing characters'    => ['2023-01-15x'],
            'trailing newline'       => ["2023-01-15\n2023-01-16"],
            'month name'             => ['2023-Jan-15'],
            'words'                  => ['January 15, 2023'],
            'relative'               => ['tomorrow'],
            'now'                    => ['now'],
            'letters'                => ['abcd-ef-gh'],
            'full width digits'      => ['２０２３-０１-１５'],
            'timestamp'              => ['1673740800'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('validDateProvider')]
    public function testParseValueValidDates(string $value, string $expected): void
    {
        $type = new DateType();
        $result = $type->parseValue($value);

        $this->assertInstanceOf(\DateTimeImmutable::class, $result);
        $this->assertSame($expected, $result->format('Y-m-d'));
        $this->assertSame('00:00:00.000000', $result->format('H:i:s.u'));
    }

    public static function validDateProvider(): array
    {
        return [
            'first day of year'           => ['2023-01-01', '2023-01-01'],
            'last day of year'            => ['2023-12-31', '2023-12-31'],
            '31 January'                  => ['2023-01-31', '2023-01-31'],
            '28 February'                 => ['2023-02-28', '2023-02-28'],
            '30 April'                    => ['2023-04-30', '2023-04-30'],
            '31 July'                     => ['2023-07-31', '2023-07-31'],
            '31 August'                   => ['2023-08-31', '2023-08-31'],
            'leap day'                    => ['2024-02-29', '2024-02-29'],
            'leap day, divisible by 400'  => ['2000-02-29', '2000-02-29'],
            'year 0001'                   => ['0001-01-01', '0001-01-01'],
            'year 9999'                   => ['9999-12-31', '9999-12-31'],
            'leading & trailing spaces'   => [' 2023-06-20 ', '2023-06-20'],
            'surrounding tab and newline' => ["\t2023-06-20\n", '2023-06-20'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('impossibleDateProvider')]
    public function testParseValueWithImpossibleDate(string $impossibleDate): void
    {
        $type = new DateType();

        $this->expectException(\Overblog\GraphQLBundle\Error\UserError::class);
        $this->expectExceptionMessageIsOrContains('Unable to parse Date. Ensure format is Y-m-d. Received: "'.$impossibleDate.'"');

        $type->parseValue($impossibleDate);
    }

    public static function impossibleDateProvider(): array
    {
        return [
            '30 February'                       => ['2023-02-30'],
            '31 February'                       => ['2023-02-31'],
            '29 February, not leap'             => ['2023-02-29'],
            '29 February, century not leap'     => ['1900-02-29'],
            '30 February, leap year'            => ['2024-02-30'],
            '31 April'                          => ['2023-04-31'],
            '31 June'                           => ['2023-06-31'],
            '31 September'                      => ['2023-09-31'],
            '31 November'                       => ['2023-11-31'],
            'year 0000'                         => ['0000-01-01'],
        ];
    }
}
