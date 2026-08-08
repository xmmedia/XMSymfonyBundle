<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Xm\SymfonyBundle\Model\Gender;

class EnumTest extends TestCase
{
    public function testSameAs(): void
    {
        $enum1 = Gender::FEMALE();
        $enum2 = Gender::FEMALE();

        $this->assertTrue($enum1->sameValueAs($enum2));
    }

    public function testSameAsFalse(): void
    {
        $enum1 = Gender::FEMALE();
        $enum2 = Gender::MALE();

        $this->assertFalse($enum1->sameValueAs($enum2));
    }
}
