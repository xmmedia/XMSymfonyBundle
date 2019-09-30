<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Security;

use Mockery;
use Symfony\Component\Security\Core\User\UserInterface;
use Xm\SymfonyBundle\Entity\User;
use Xm\SymfonyBundle\Security\Exception\AccountInactiveException;
use Xm\SymfonyBundle\Security\Exception\AccountNotVerifiedException;
use Xm\SymfonyBundle\Security\UserChecker;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UserCheckerTest extends BaseTestCase
{
    public function testCheckPreAuth(): void
    {
        $checker = new UserChecker();

        $user = Mockery::mock(User::class);
        $user->shouldNotReceive('verified');
        $user->shouldNotReceive('active');

        $checker->checkPreAuth($user);
    }

    public function testCheckPreAuthDiffUser(): void
    {
        $checker = new UserChecker();

        $user = Mockery::mock(UserInterface::class);
        $user->shouldNotReceive('verified');
        $user->shouldNotReceive('active');

        $checker->checkPreAuth($user);
    }

    public function testCheckPostAuthValid(): void
    {
        $checker = new UserChecker();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();
        $user->shouldReceive('active')
            ->once()
            ->andReturnTrue();

        $checker->checkPostAuth($user);
    }

    public function testCheckPostAuthNotVerified(): void
    {
        $checker = new UserChecker();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnfalse();
        $user->shouldNotReceive('active');

        $this->expectException(AccountNotVerifiedException::class);

        $checker->checkPostAuth($user);
    }

    public function testCheckPostAuthNotActive(): void
    {
        $checker = new UserChecker();

        $user = Mockery::mock(User::class);
        $user->shouldReceive('verified')
            ->once()
            ->andReturnTrue();
        $user->shouldReceive('active')
            ->once()
            ->andReturnFalse();

        $this->expectException(AccountInactiveException::class);

        $checker->checkPostAuth($user);
    }

    public function testCheckPostAuthDiffUser(): void
    {
        $checker = new UserChecker();

        $user = Mockery::mock(UserInterface::class);
        $user->shouldNotReceive('verified');
        $user->shouldNotReceive('active');

        $checker->checkPostAuth($user);
    }
}
