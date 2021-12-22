<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Type;

use GraphQL\Language\AST\StringValueNode;
use GraphQL\Language\AST\VariableNode;
use PHPUnit\Framework\TestCase;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Type\DateTimeType;

class DateTimeTypeTest extends TestCase
{
    /**
     * @dataProvider dateProvider
     */
    public function testSerialize(?\DateTimeInterface $value, ?string $expected): void
    {
        $result = (new DateTimeType())->serialize($value);

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider dateProvider
     */
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

    /**
     * @dataProvider dateProvider
     */
    public function testParseLiteral(?\DateTimeInterface $expected, ?string $value): void
    {
        $valueNode = new StringValueNode([]);
        $valueNode->value = $value;

        $result = (new DateTimeType())->parseLiteral($valueNode);

        $this->assertEquals($expected, $result);
    }

    public function dateProvider(): \Generator
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

    public function testParseValueInvalid(): void
    {
        $this->expectException(\Exception::class);

        (new DateTimeType())->parseValue('asdf');
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
