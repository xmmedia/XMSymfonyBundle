<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User;

use Xm\SymfonyBundle\Model\User\Token;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeVo;

class TokenTest extends BaseTestCase
{
    public function testFromString(): void
    {
        $faker = $this->faker();
        $tokenString = $faker->string(25);

        $token = Token::fromString($tokenString);

        $this->assertEquals($tokenString, $token->token());
        $this->assertEquals($tokenString, $token->toString());
        $this->assertEquals($tokenString, (string) $token);
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Token::fromString('');
    }

    public function testSameValueAs(): void
    {
        $faker = $this->faker();
        $tokenString = $faker->string(25);

        $token1 = Token::fromString($tokenString);
        $token2 = Token::fromString($tokenString);

        $this->assertTrue($token1->sameValueAs($token2));
    }

    public function testSameValueAsFalse(): void
    {
        $faker = $this->faker();

        $token1 = Token::fromString($faker->string(25));
        $token2 = Token::fromString($faker->string(25));

        $this->assertFalse($token1->sameValueAs($token2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $faker = $this->faker();

        $token = Token::fromString($faker->string(25));

        $this->assertFalse($token->sameValueAs(FakeVo::create()));
    }
}
