<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    public function testOneOf(): void
    {
        $this->assertTrue(TestEnum::FEMALE()->oneOf([TestEnum::MALE(), TestEnum::FEMALE()]));
    }

    public function testOneOfFalse(): void
    {
        $this->assertFalse(TestEnum::FEMALE()->oneOf([TestEnum::MALE()]));
    }

    public function testSameAs(): void
    {
        $enum1 = TestEnum::FEMALE();
        $enum2 = TestEnum::FEMALE();

        $this->assertTrue($enum1->sameValueAs($enum2));
    }

    public function testSameAsFalse(): void
    {
        $enum1 = TestEnum::FEMALE();
        $enum2 = TestEnum::MALE();

        $this->assertFalse($enum1->sameValueAs($enum2));
    }
}
