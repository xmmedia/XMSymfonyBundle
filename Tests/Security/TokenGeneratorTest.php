<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Security;

use PHPUnit\Framework\TestCase;
use Xm\SymfonyBundle\Security\TokenGenerator;

class TokenGeneratorTest extends TestCase
{
    public function test(): void
    {
        $token = (new TokenGenerator())();

        $this->assertEquals(
            43,
            \strlen($token->toString()),
            'The token length is not 43.'
        );
    }
}
