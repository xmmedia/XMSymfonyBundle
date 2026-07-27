<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use PHPUnit\Framework\TestCase;
use Xm\SymfonyBundle\Model\ValueObject;
use Xm\SymfonyBundle\Model\ValueObjectEnum;
use Xm\SymfonyBundle\Model\ValueObjectEnumTrait;

class ValueObjectEnumTest extends TestCase
{
    public function testStringValues(): void
    {
        $this->assertEquals(['a', 'b'], StringEnum::values());
    }

    public function testStringNames(): void
    {
        $this->assertEquals(['Alpha', 'Beta'], StringEnum::names());
    }

    public function testStringNamesToValues(): void
    {
        $this->assertEquals(
            ['Alpha' => 'a', 'Beta' => 'b'],
            StringEnum::namesToValues(),
        );
    }

    public function testIntValues(): void
    {
        $this->assertEquals([1, 2], IntEnum::values());
    }

    public function testIntNames(): void
    {
        $this->assertEquals(['One', 'Two'], IntEnum::names());
    }

    public function testIntNamesToValues(): void
    {
        $this->assertEquals(
            ['One' => 1, 'Two' => 2],
            IntEnum::namesToValues(),
        );
    }

    public function testInstanceOf(): void
    {
        $this->assertInstanceOf(ValueObjectEnum::class, StringEnum::Alpha);
        $this->assertInstanceOf(ValueObject::class, StringEnum::Alpha);
        $this->assertInstanceOf(\BackedEnum::class, StringEnum::Alpha);

        $this->assertInstanceOf(ValueObjectEnum::class, IntEnum::One);
    }

    public function testSameValueAs(): void
    {
        $this->assertTrue(StringEnum::Alpha->sameValueAs(StringEnum::Alpha));
        $this->assertTrue(IntEnum::One->sameValueAs(IntEnum::One));
    }

    public function testSameValueAsDiffCase(): void
    {
        $this->assertFalse(StringEnum::Alpha->sameValueAs(StringEnum::Beta));
    }

    public function testSameValueAsDiffEnum(): void
    {
        $this->assertFalse(StringEnum::Alpha->sameValueAs(IntEnum::One));
    }

    public function testSameValueAsOtherValueObject(): void
    {
        $other = new class implements ValueObject {
            public function sameValueAs(ValueObject $other): bool
            {
                return $this === $other;
            }
        };

        $this->assertFalse(StringEnum::Alpha->sameValueAs($other));
    }
}

enum StringEnum: string implements ValueObjectEnum
{
    use ValueObjectEnumTrait;

    case Alpha = 'a';
    case Beta = 'b';
}

enum IntEnum: int implements ValueObjectEnum
{
    use ValueObjectEnumTrait;

    case One = 1;
    case Two = 2;
}
