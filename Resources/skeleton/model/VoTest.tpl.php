<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $name_class; ?>;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeVo;

class <?= $class_name; ?> extends BaseTestCase
{
    public function testFromString(): void
    {
        $faker = $this->faker();
        $nameStr = $faker->name();

        $name = Name::fromString($nameStr);

        $this->assertSame($nameStr, $name->name());
        $this->assertSame($nameStr, $name->toString());
        $this->assertSame($nameStr, (string) $name);
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Name::fromString('');
    }

    public function testTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        Name::fromString('a');
    }

    public function testTooLong(): void
    {
        $faker = $this->faker();

        $this->expectException(\InvalidArgumentException::class);

        Name::fromString($faker->string(51));
    }

    public function testTrailingWhiteSpace(): void
    {
        $faker = $this->faker();
        $nameStr = $faker->name();

        $name = Name::fromString($nameStr.'   ');

        $this->assertSame($nameStr, $name->name());
        $this->assertSame($nameStr, $name->toString());
        $this->assertSame($nameStr, (string) $name);
    }

    public function testSameValueAs(): void
    {
        $faker = $this->faker();
        $nameStr = $faker->name();

        $name1 = Name::fromString($nameStr);
        $name2 = Name::fromString($nameStr);

        $this->assertTrue($name1->sameValueAs($name2));
    }

    public function testSameValueAsFalse(): void
    {
        $faker = $this->faker();

        $name1 = Name::fromString($faker->unique()->name());
        $name2 = Name::fromString($faker->unique()->name());

        $this->assertFalse($name1->sameValueAs($name2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $faker = $this->faker();

        $name = Name::fromString($faker->name());

        $this->assertFalse($name->sameValueAs(FakeVo::create()));
    }
}
