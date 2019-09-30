<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Security;

use Mockery;
use Xm\SymfonyBundle\Entity\User;
use Xm\SymfonyBundle\Entity\UserToken;
use Xm\SymfonyBundle\Model\User\Exception\InvalidToken;
use Xm\SymfonyBundle\Model\User\Exception\TokenHasExpired;
use Xm\SymfonyBundle\Model\User\Token;
use Xm\SymfonyBundle\Projection\User\UserTokenFinder;
use Xm\SymfonyBundle\Security\TokenValidator;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class TokenValidatorTest extends BaseTestCase
{
    public function testValid(): void
    {
        $token = Token::fromString('string');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('active')
            ->once()
            ->andReturnTrue();

        $userToken = Mockery::mock(UserToken::class);
        $userToken->shouldReceive('user')
            ->once()
            ->andReturn($user);
        $userToken->shouldReceive('generatedAt')
            ->once()
            ->andReturn(new \DateTimeImmutable('-5 hours'));

        $tokenFinder = Mockery::mock(UserTokenFinder::class);
        $tokenFinder->shouldReceive('find')
            ->once()
            ->with($token->toString())
            ->andReturn($userToken);

        $result = (new TokenValidator($tokenFinder))->validate($token);

        $this->assertInstanceOf(User::class, $result);
    }

    public function testTokenDoesntExist(): void
    {
        $token = Token::fromString('string');

        $tokenFinder = Mockery::mock(UserTokenFinder::class);
        $tokenFinder->shouldReceive('find')
            ->with($token->toString())
            ->andReturnNull();

        $this->expectException(InvalidToken::class);

        (new TokenValidator($tokenFinder))->validate($token);
    }

    public function testUserInactive(): void
    {
        $token = Token::fromString('string');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('active')
            ->andReturnFalse();

        $userToken = Mockery::mock(UserToken::class);
        $userToken->shouldReceive('user')
            ->andReturn($user);

        $tokenFinder = Mockery::mock(UserTokenFinder::class);
        $tokenFinder->shouldReceive('find')
            ->with($token->toString())
            ->andReturn($userToken);

        $this->expectException(InvalidToken::class);

        (new TokenValidator($tokenFinder))->validate($token);
    }

    public function testExpiredToken(): void
    {
        $token = Token::fromString('string');

        $user = Mockery::mock(User::class);
        $user->shouldReceive('active')
            ->andReturnTrue();

        $userToken = Mockery::mock(UserToken::class);
        $userToken->shouldReceive('user')
            ->andReturn($user);
        $userToken->shouldReceive('generatedAt')
            ->andReturn(new \DateTimeImmutable('-48 hours'));

        $tokenFinder = Mockery::mock(UserTokenFinder::class);
        $tokenFinder->shouldReceive('find')
            ->with($token->toString())
            ->andReturn($userToken);

        $this->expectException(TokenHasExpired::class);

        (new TokenValidator($tokenFinder))->validate($token);
    }
}
