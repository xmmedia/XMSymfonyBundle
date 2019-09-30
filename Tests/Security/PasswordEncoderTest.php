<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Security;

use Mockery;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Security\PasswordEncoder;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class PasswordEncoderTest extends BaseTestCase
{
    /**
     * @dataProvider roleProvider
     */
    public function test(Role $role): void
    {
        $faker = $this->faker();

        $passwordEncoder = Mockery::mock(UserPasswordEncoderInterface::class);
        $passwordEncoder->shouldReceive('encodePassword')
            ->withArgs(function ($user, $password) use ($role): bool {
                $this->assertEquals($role, $user->firstRole());

                return true;
            })
            ->andReturn('encoded-password');

        (new PasswordEncoder($passwordEncoder))($role, $faker->password);
    }

    public function roleProvider(): \Generator
    {
        foreach (Role::getEnumerators() as $role) {
            yield [$role];
        }
    }
}
