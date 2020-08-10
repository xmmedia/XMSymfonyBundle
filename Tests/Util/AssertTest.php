<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Util;

use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Util\Assert;

class AssertTest extends BaseTestCase
{
    /**
     * @dataProvider okayPasswordProvider
     */
    public function testPasswordComplexityOkay(string $password): void
    {
        Assert::passwordComplexity($password, []);

        $this->assertTrue(true);
    }

    public function okayPasswordProvider(): \Generator
    {
        $faker = $this->faker();

        yield [$faker->password];
        yield ['oh98yih87tg8ybo97c976c98'];
    }

    /**
     * @dataProvider badPasswordProvider
     */
    public function testPasswordComplexityBad(
        string $password,
        array $userData
    ): void {
        $this->expectException(\InvalidArgumentException::class);

        Assert::passwordComplexity($password, $userData);
    }

    public function badPasswordProvider(): \Generator
    {
        $faker = $this->faker();

        yield ['123456', []];
        yield ['asdf@asdf.com'.substr($faker->password, 0, 5), ['asdf@asdf.com']];
    }

    public function testCompromisedPasswordCompromisedRealCallNotCompromised(): void
    {
        $faker = $this->faker();

        Assert::compromisedPassword($faker->password);

        $this->assertTrue(true);
    }

    public function testCompromisedPasswordCompromisedRealCallCompromised(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The entered password has been compromised.');

        Assert::compromisedPassword('123456');
    }

    public function testCompromisedPasswordCompromisedNotCompromised(): void
    {
        $faker = $this->faker();

        $httpClient = new MockHttpClient([
            new MockResponse('0044F0E373D616646005C6B320FB3B1AC10:1'),
        ]);

        Assert::compromisedPassword($faker->password, $httpClient);

        $this->assertTrue(true);
    }

    public function testCompromisedPasswordCompromisedBadResponse(): void
    {
        $faker = $this->faker();

        $httpClient = new MockHttpClient([
            new MockResponse('test'),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to check for compromised password.');

        Assert::compromisedPassword($faker->password, $httpClient);
    }

    public function testCompromisedPasswordCompromised1Time(): void
    {
        $faker = $this->faker();

        $password = $faker->password;
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

        $password = $faker->password;
        $hashPassword = strtoupper(sha1($password));

        $httpClient = new MockHttpClient([
            new MockResponse(substr($hashPassword, 5).':3'),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The entered password has been compromised.');

        Assert::compromisedPassword($password, $httpClient);
    }

    public function testCompromisedPasswordTimeout(): void
    {
        $faker = $this->faker();

        $body = function (): \Generator {
            yield '';
        };
        $httpClient = new MockHttpClient([
            new MockResponse($body()),
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to check for compromised password.');

        Assert::compromisedPassword($faker->password, $httpClient);
    }
}
