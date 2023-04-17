<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Type;

use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Type\UuidType;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UuidTypeTest extends BaseTestCase
{
    /**
     * @dataProvider uuidProvider
     */
    public function testSerialize($value, ?string $expected): void
    {
        $result = (new UuidType())->serialize($value);

        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidUuidProvider
     */
    public function testSerializeInvalid(string|null|array $value): void
    {
        $this->expectException(Error::class);

        (new UuidType())->serialize($value);
    }

    public function testSerializerUserId(): void
    {
        $faker = $this->faker();

        $fakeId = $faker->fakeId();
        $result = (new UuidType())->serialize($fakeId);

        $this->assertEquals($fakeId->toString(), $result);
    }

    /**
     * @dataProvider uuidProvider
     */
    public function testParseValue($value, ?string $expected): void
    {
        $result = (new UuidType())->parseValue($value);

        $this->assertEquals($expected, $result);
    }

    public function uuidProvider(): \Generator
    {
        $faker = $this->faker();

        $fakeId = $faker->fakeId();

        yield [
            $fakeId,
            $fakeId->toString(),
        ];

        yield [
            $fakeId->toString(),
            $fakeId->toString(),
        ];
    }

    /**
     * @dataProvider invalidUuidProvider
     */
    public function testParseValueInvalid(string|null|array $value): void
    {
        $this->expectException(Error::class);

        (new UuidType())->parseValue($value);
    }

    public function testParseLiteral(): void
    {
        $faker = $this->faker();
        $fakeId = $faker->fakeId();

        $node = new StringValueNode([]);
        $node->value = $fakeId->toString();

        $result = (new UuidType())->parseLiteral($node);

        $this->assertEquals($fakeId->toString(), $result);
    }

    public function testParseLiteralInvalid(): void
    {
        $node = new StringValueNode([]);
        $node->value = 'string';

        $this->expectException(Error::class);

        (new UuidType())->parseLiteral($node);
    }

    public function invalidUuidProvider(): \Generator
    {
        yield [
            'string',
            null,
        ];

        yield [
            null,
            null,
        ];

        yield [
            [],
            null,
        ];
    }

    public function testParseLiteralNotStringValueNode(): void
    {
        $result = (new UuidType())->parseLiteral('string');

        $this->assertNull($result);
    }

    public function testAliases(): void
    {
        $result = UuidType::getAliases();

        $this->assertEquals(['UUID'], $result);
    }
}
