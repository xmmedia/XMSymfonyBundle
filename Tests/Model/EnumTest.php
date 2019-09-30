<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Xm\SymfonyBundle\Model\Enum;

class EnumTest extends TestCase
{
    public function testSameAs(): void
    {
        /** @var Enum $enum1 */
        $enum1 = $this->getMockForAbstractClass(Enum::class, [], '', false);
        $enum2 = $this->getMockForAbstractClass(Enum::class, [], '', false);

        $this->assertTrue($enum1->sameValueAs($enum2));
    }
}
