<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Type;

use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\VariableNode;
use Overblog\GraphQLBundle\Error\UserError;
use PHPUnit\Framework\TestCase;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Type\DateTimeType;

class DateTimeTypeTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dateProvider')]
    public function testSerialize(?\DateTimeInterface $value, ?string $expected): void
    {
        $result = (new DateTimeType())->serialize($value);

        $this->assertEquals($expected, $result);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dateProvider')]
    public function testParseValue(?\DateTimeInterface $expected, ?string $value): void
    {
        $result = (new DateTimeType())->parseValue($value);

        $this->assertEquals($expected, $result);
    }

    public function testParseValueNonUtc(): void
    {
        $result = (new DateTimeType())->parseValue('2019-01-01T10:53:32-07:00');

        $this->assertEquals(new \DateTimeImmutable('2019-01-01 17:53:32'), $result);
    }

    public static function dateProvider(): \Generator
    {
        yield [
            new \DateTime('2019-01-01'),
            '2019-01-01T00:00:00+00:00',
        ];

        yield [
            new \DateTimeImmutable('2019-01-01'),
            '2019-01-01T00:00:00+00:00',
        ];

        yield [
            new \DateTimeImmutable('2019-01-01 10:53:32'),
            '2019-01-01T10:53:32+00:00',
        ];

        yield [
            null,
            null,
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dateProviderParseLiteral')]
    public function testParseLiteral(?\DateTimeInterface $expected, string $value): void
    {
        $valueNode = new StringValueNode([]);
        $valueNode->value = $value;

        $result = (new DateTimeType())->parseLiteral($valueNode);

        $this->assertEquals($expected, $result);
    }

    public static function dateProviderParseLiteral(): \Generator
    {
        yield [
            new \DateTime('2019-01-01'),
            '2019-01-01T00:00:00+00:00',
        ];

        yield [
            new \DateTimeImmutable('2019-01-01'),
            '2019-01-01T00:00:00+00:00',
        ];

        yield [
            new \DateTimeImmutable('2019-01-01 10:53:32'),
            '2019-01-01T10:53:32+00:00',
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('nonStringProvider')]
    public function testParseValueNonString(mixed $value): void
    {
        $this->expectException(UserError::class);
        $this->expectExceptionMessageIsOrContains('Unable to parse DateTime.');

        (new DateTimeType())->parseValue($value);
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
        $this->assertNull((new DateTimeType())->parseValue($empty));
    }

    public static function emptyProvider(): array
    {
        return [
            [null],
            [''],
            [' '],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('invalidProvider')]
    public function testParseValueInvalid(string $value): void
    {
        $this->expectException(UserError::class);
        $this->expectExceptionMessageIsOrContains('Unable to parse DateTime. Received: "'.$value.'"');

        (new DateTimeType())->parseValue($value);
    }

    public static function invalidProvider(): array
    {
        return [
            'letters'                => ['asdf'],
            'month 13'               => ['2023-13-01T00:00:00Z'],
            'hour 25'                => ['2023-01-15T25:00:00Z'],
            '30 February'            => ['2023-02-30T00:00:00Z'],
            '29 February, not leap'  => ['2023-02-29T10:00:00-07:00'],
            '31 April'               => ['2023-04-31T00:00:00+00:00'],
            '31 April, without time' => ['2023-04-31'],
        ];
    }

    public function testParseValueLeapDay(): void
    {
        $result = (new DateTimeType())->parseValue('2024-02-29T10:00:00-07:00');

        $this->assertEquals(new \DateTimeImmutable('2024-02-29 17:00:00'), $result);
    }

    public function testParseValueTrimmed(): void
    {
        $result = (new DateTimeType())->parseValue(' 2019-01-01T10:53:32+00:00 ');

        $this->assertEquals(new \DateTimeImmutable('2019-01-01 10:53:32'), $result);
    }

    public function testParseLiteralNotStringValueNode(): void
    {
        $result = (new DateTimeType())->parseLiteral(new VariableNode([]));

        $this->assertNull($result);
    }

    public function testAliases(): void
    {
        $result = DateTimeType::getAliases();

        $this->assertEquals(['DateTime'], $result);
    }
}
