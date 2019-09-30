<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Resolver;

use Xm\SymfonyBundle\Infrastructure\GraphQl\Resolver\CountryResolver;
use Xm\SymfonyBundle\Model\Country;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class CountryResolverTest extends BaseTestCase
{
    public function test(): void
    {
        $all = (new CountryResolver())();

        $this->assertCount(2, $all);
        $this->assertInstanceOf(Country::class, $all[0]);
    }
}
