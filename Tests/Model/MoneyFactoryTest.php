<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Xm\SymfonyBundle\Model\MoneyFactory;

class MoneyFactoryTest extends TestCase
{
    /**
     * @dataProvider intDataProvider
     */
    public function testFromInt(int $cents, string $expected): void
    {
        $res = MoneyFactory::fromInt($cents);

        $this->assertEquals($expected, $res->getAmount());
        $this->assertEquals('CAD', $res->getCurrency());
    }

    public static function intDataProvider(): array
    {
        return [
            [533, '533'],
            [5330, '5330'],
            [53309, '53309'],
            [5, '5'],
            [53, '53'],
        ];
    }

    public function testZero(): void
    {
        $res = MoneyFactory::zero();

        $this->assertEquals('0', $res->getAmount());
        $this->assertEquals('CAD', $res->getCurrency());
    }
}
