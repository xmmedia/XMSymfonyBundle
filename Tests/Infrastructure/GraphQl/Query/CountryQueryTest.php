<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Query;

use Xm\SymfonyBundle\Infrastructure\GraphQl\Query\CountryQuery;
use Xm\SymfonyBundle\Model\Country;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class CountryQueryTest extends BaseTestCase
{
    public function test(): void
    {
        $all = (new CountryQuery())();

        $this->assertCount(2, $all);
        $this->assertInstanceOf(Country::class, $all[0]);
    }
}
