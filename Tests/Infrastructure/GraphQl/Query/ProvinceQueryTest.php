<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Query;

use Xm\SymfonyBundle\Infrastructure\GraphQl\Query\ProvinceQuery;
use Xm\SymfonyBundle\Model\Province;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class ProvinceQueryTest extends BaseTestCase
{
    public function test(): void
    {
        $all = (new ProvinceQuery())();

        $this->assertCount(64, $all);
        $this->assertInstanceOf(Province::class, $all[0]);
    }
}
