<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Util;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\PasswordStrengthFake;
use Xm\SymfonyBundle\Util\Assert;

class AssertTest extends BaseTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('okayPasswordProvider')]
    public function testPasswordComplexityOkay(string $password): void
    {
        Assert::passwordComplexity(
            $password,
            [],
            null,
            new PasswordStrengthFake(),
        );

        $this->assertTrue(true);
    }

    public static function okayPasswordProvider(): \Generator
    {
        $faker = self::faker();

        yield [$faker->password()];
        yield ['oh98yih87tg8ybo97c976c98'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('badPasswordProvider')]
    public function testPasswordComplexityBad(
        string $password,
        array $userData,
    ): void {
        $this->expectException(\InvalidArgumentException::class);

        Assert::passwordComplexity(
            $password,
            $userData,
            null,
            new PasswordStrengthFake(0),
        );
    }

    public static function badPasswordProvider(): \Generator
    {
        $faker = self::faker();

        yield ['123456', []];
        yield ['asdf@asdf.com'.substr($faker->password(), 0, 5), ['asdf@asdf.com']];
    }

    public function testCompromisedPasswordCompromisedRealCallNotCompromised(): void
    {
        $faker = $this->faker();

        Assert::compromisedPassword($faker->password());

        $this->assertTrue(true);
    }

    public function testCompromisedPasswordCompromisedRealCallCompromised(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('The entered password has been compromised.');

        Assert::compromisedPassword('123456');
    }

    public function testCompromisedPasswordCompromisedNotCompromised(): void
    {
        $faker = $this->faker();

        $httpClient = new MockHttpClient([
            new MockResponse('0044F0E373D616646005C6B320FB3B1AC10:1'),
        ]);

        Assert::compromisedPassword($faker->password(), $httpClient);

        $this->assertTrue(true);
    }

    public function testCompromisedPasswordCompromisedBadResponse(): void
    {
        $faker = $this->faker();

        $httpClient = new MockHttpClient([
            new MockResponse('test'),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Unable to check for compromised password.');

        Assert::compromisedPassword($faker->password(), $httpClient);
    }

    public function testCompromisedPasswordCompromised1Time(): void
    {
        $faker = $this->faker();

        $password = $faker->password();
        $hashPassword = strtoupper(sha1($password));

        $httpClient = new MockHttpClient([
            new MockResponse(substr($hashPassword, 5).':1'),
        ]);

        Assert::compromisedPassword($password, $httpClient);

        $this->assertTrue(true);
    }

    public function testCompromisedPasswordCompromised3Times(): void
    {
        $faker = $this->faker();

        $password = $faker->password();
        $hashPassword = strtoupper(sha1($password));

        $httpClient = new MockHttpClient([
            new MockResponse(substr($hashPassword, 5).':3'),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('The entered password has been compromised.');

        Assert::compromisedPassword($password, $httpClient);
    }

    public function testCompromisedPasswordTimeout(): void
    {
        $faker = $this->faker();

        $body = static function (): \Generator {
            yield '';
        };
        $httpClient = new MockHttpClient([
            new MockResponse($body()),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Unable to check for compromised password.');

        Assert::compromisedPassword($faker->password(), $httpClient);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('allScalarRecursiveValidProvider')]
    public function testAllScalarRecursiveValid(array $value): void
    {
        Assert::allScalarRecursive($value);

        $this->addToAssertionCount(1);
    }

    public static function allScalarRecursiveValidProvider(): \Generator
    {
        yield 'empty'         => [[]];
        yield 'scalars'       => [['a' => 'string', 'b' => 1, 'c' => 1.5, 'd' => true, 'e' => null]];
        yield 'nested'        => [['a' => ['b' => 1, 'c' => ['d' => 'e', 'f' => null]]]];
        yield 'nested empty'  => [['a' => []]];
        yield 'list'          => [[1, 2, [3, 4]]];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('allScalarRecursiveInvalidProvider')]
    public function testAllScalarRecursiveInvalid(array $value, string $message): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains($message);

        Assert::allScalarRecursive($value);
    }

    public static function allScalarRecursiveInvalidProvider(): \Generator
    {
        yield 'object'             => [['a' => new \stdClass()], 'Expected a scalar. Got: stdClass for a'];
        yield 'nested object'      => [['a' => ['b' => new \stdClass()]], 'Expected a scalar. Got: stdClass for b'];
        yield 'deeply nested'      => [['a' => ['b' => ['c' => new \DateTimeImmutable()]]], 'for c'];
        yield 'after valid values' => [['a' => 1, 'b' => [2, new \stdClass()]], 'for 1'];
    }

    public function testAllScalarRecursiveCustomMessage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Template data must be scalars. Got stdClass');

        Assert::allScalarRecursive(['a' => ['b' => new \stdClass()]], 'Template data must be scalars. Got %s');
    }
}
