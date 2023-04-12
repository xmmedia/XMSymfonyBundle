<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Util;

use PHPUnit\Framework\TestCase;
use Xm\SymfonyBundle\Util\Json;

class JsonTest extends TestCase
{
    /**
     * @dataProvider provider
     */
    public function testEncode(string|float|int|bool|array $value, string $expected): void
    {
        $this->assertEquals($expected, Json::encode($value));
    }

    /**
     * @dataProvider provider
     */
    public function testDecode(string|float|int|bool|array $expected, string $json): void
    {
        $this->assertEquals($expected, Json::decode($json));
    }

    public static function provider(): \Generator
    {
        yield ['😱', '"😱"'];
        yield ['/', '"/"'];
        yield [(float) -1, '-1.0'];
        yield [-1.0, '-1.0'];
        yield [-1, '-1'];
        yield [0, '0'];
        yield [0.1, '0.1'];
        yield [true, 'true'];
        yield [1343232323, '1343232323'];
        yield ['<>\'&"', '"<>\'&\""'];
        yield [[[1, 2, 3]], '[[1,2,3]]'];
    }

    public function testEncodeError(): void
    {
        $this->expectException(\JsonException::class);

        Json::encode("\xB1\x31");
    }

    public function testDecodeError(): void
    {
        $this->expectException(\JsonException::class);

        Json::decode('asdf');
    }
}
