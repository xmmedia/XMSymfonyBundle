<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Type;

use GraphQL\Error\Error;
use GraphQL\Language\AST\IntValueNode;
use GraphQL\Language\AST\StringValueNode;
use Ramsey\Uuid\Uuid;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Type\UuidTypeTrait;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UuidTypeTraitTest extends BaseTestCase
{
    private function createTraitInstance(): object
    {
        return new class {
            use UuidTypeTrait;

            public function parseValue($value): ?string
            {
                // Minimal implementation for testing
                return \is_string($value) && Uuid::isValid($value) ? $value : null;
            }
        };
    }

    public function testSerializeWithUuidInterface(): void
    {
        $faker = $this->faker();
        $fakeId = $faker->fakeId();

        $trait = $this->createTraitInstance();
        $result = $trait->serialize($fakeId);

        $this->assertEquals($fakeId->toString(), $result);
    }

    public function testSerializeWithRamseyUuid(): void
    {
        $uuid = Uuid::uuid4();

        $trait = $this->createTraitInstance();
        $result = $trait->serialize($uuid);

        $this->assertEquals($uuid->toString(), $result);
    }

    public function testSerializeWithValidString(): void
    {
        $uuidString = '550e8400-e29b-41d4-a716-446655440000';

        $trait = $this->createTraitInstance();
        $result = $trait->serialize($uuidString);

        $this->assertEquals($uuidString, $result);
    }

    public function testSerializeWithInvalidStringThrowsError(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Cannot serialize value as UUID');

        $trait = $this->createTraitInstance();
        $trait->serialize('not-a-uuid');
    }

    public function testSerializeWithInvalidTypeThrowsError(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Cannot serialize value as UUID');

        $trait = $this->createTraitInstance();
        $trait->serialize(12345);
    }

    public function testSerializeWithNullThrowsError(): void
    {
        $this->expectException(Error::class);
        $this->expectExceptionMessage('Cannot serialize value as UUID');

        $trait = $this->createTraitInstance();
        $trait->serialize(null);
    }

    public function testParseLiteralWithStringValueNode(): void
    {
        $faker = $this->faker();
        $fakeId = $faker->fakeId();

        $node = new StringValueNode([]);
        $node->value = $fakeId->toString();

        $trait = $this->createTraitInstance();
        $result = $trait->parseLiteral($node);

        $this->assertEquals($fakeId->toString(), $result);
    }

    public function testParseLiteralWithNonStringValueNode(): void
    {
        $node = new IntValueNode([]);
        $node->value = '123';

        $trait = $this->createTraitInstance();
        $result = $trait->parseLiteral($node);

        $this->assertNull($result);
    }

    public function testParseLiteralWithVariables(): void
    {
        $faker = $this->faker();
        $fakeId = $faker->fakeId();

        $node = new StringValueNode([]);
        $node->value = $fakeId->toString();

        $trait = $this->createTraitInstance();
        $result = $trait->parseLiteral($node, ['someVariable' => 'value']);

        $this->assertEquals($fakeId->toString(), $result);
    }
}
