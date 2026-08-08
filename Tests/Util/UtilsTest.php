<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Util;

use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Util\Utils;

class UtilsTest extends BaseTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('serializeValidProvider')]
    public function testSerializeValid(null|bool|string|float|int|array|ClassWithToString|ClassWithGetValue|ClassWithToArray $input, null|bool|string|float|int|array $expected): void
    {
        $this->assertSame($expected, Utils::serialize($input));
    }

    public static function serializeValidProvider(): \Generator
    {
        yield [null, null];
        yield [true, true];
        yield ['string', 'string'];
        yield [1.3432, 1.3432];
        yield [2, 2];
        yield [['array'], ['array']];
        yield [new ClassWithToString(), 'string'];
        yield [new ClassWithGetValue(), 'string'];
        yield [new ClassWithToArray(), ['array']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('serializeInvalidProvider')]
    public function testSerializeInvalid(\stdClass $input): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Utils::serialize($input);
    }

    public static function serializeInvalidProvider(): \Generator
    {
        yield [new \stdClass()];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('printSafeProvider')]
    public function testPrintSafe(\stdClass|\Closure|array|string|null|bool|int|float $var, string $type): void
    {
        $this->assertSame($type, Utils::printSafe($var));
    }

    public static function printSafeProvider(): \Generator
    {
        yield [new \stdClass(), 'instance of stdClass'];
        yield [
            static function (): void {
            },
            'instance of Closure',
        ];
        yield [[], 'array'];
        yield ['', '(empty string)'];
        yield [null, 'NULL'];
        yield [false, 'false (boolean)'];
        yield [true, 'true (boolean)'];
        yield ['string', 'string'];
        yield [12, '12'];
        yield [1.3234, '1.3234'];
    }
}

class ClassWithToString
{
    public function __toString(): string
    {
        return 'string';
    }
}

class ClassWithGetValue
{
    public function getValue(): string
    {
        return 'string';
    }
}

class ClassWithToArray
{
    public function toArray(): array
    {
        return ['array'];
    }
}
