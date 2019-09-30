<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Resolver;

use Xm\SymfonyBundle\Infrastructure\GraphQl\Resolver\ProvinceResolver;
use Xm\SymfonyBundle\Model\Province;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class ProvinceResolverTest extends BaseTestCase
{
    public function test(): void
    {
        $all = (new ProvinceResolver())();

        $this->assertCount(64, $all);
        $this->assertInstanceOf(Province::class, $all[0]);
    }
}
