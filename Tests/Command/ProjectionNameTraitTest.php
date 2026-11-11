<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Command;

use PHPUnit\Framework\Attributes\DataProvider;
use Xm\SymfonyBundle\Command\ProjectionNameTrait;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class ProjectionNameTraitTest extends BaseTestCase
{
    #[DataProvider('nameProvider')]
    public function testProjectionName(string $name, string $expected): void
    {
        $command = new class {
            use ProjectionNameTrait;

            public function name(string $name): string
            {
                return self::projectionName($name);
            }
        };

        $this->assertSame($expected, $command->name($name));
    }

    public static function nameProvider(): \Generator
    {
        yield 'without suffix'          => ['user', 'user_projection'];
        yield 'with suffix'             => ['user_projection', 'user_projection'];
        yield 'suffix in the middle'    => ['user_projection_token', 'user_projection_token_projection'];
        yield 'without the underscore'  => ['userprojection', 'userprojection_projection'];
        yield 'only the suffix'         => ['_projection', '_projection'];
    }
}
