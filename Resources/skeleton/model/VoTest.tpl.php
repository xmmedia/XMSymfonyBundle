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

        $<?= $name_property ?> = <?= $name_class_short ?>::fromString($nameStr);

        $this->assertSame($nameStr, $<?= $name_property ?>->name());
        $this->assertSame($nameStr, $<?= $name_property ?>->toString());
        $this->assertSame($nameStr, (string) $<?= $name_property ?>);
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        <?= $name_class_short ?>::fromString('');
    }

    public function testTooShort(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        <?= $name_class_short ?>::fromString('a');
    }

    public function testTooLong(): void
    {
        $faker = $this->faker();

        $this->expectException(\InvalidArgumentException::class);

        <?= $name_class_short ?>::fromString($faker->string(51));
    }

    public function testTrailingWhiteSpace(): void
    {
        $faker = $this->faker();
        $nameStr = $faker->name();

        $<?= $name_property ?> = <?= $name_class_short ?>::fromString($nameStr.'   ');

        $this->assertSame($nameStr, $<?= $name_property ?>->name());
        $this->assertSame($nameStr, $<?= $name_property ?>->toString());
        $this->assertSame($nameStr, (string) $<?= $name_property ?>);
    }

    public function testSameValueAs(): void
    {
        $faker = $this->faker();
        $nameStr = $faker->name();

        $name1 = <?= $name_class_short ?>::fromString($nameStr);
        $name2 = <?= $name_class_short ?>::fromString($nameStr);

        $this->assertTrue($name1->sameValueAs($name2));
    }

    public function testSameValueAsFalse(): void
    {
        $faker = $this->faker();

        $<?= $name_property ?>1 = <?= $name_class_short ?>::fromString($faker->unique()->name());
        $<?= $name_property ?>2 = <?= $name_class_short ?>::fromString($faker->unique()->name());

        $this->assertFalse($<?= $name_property ?>1->sameValueAs($<?= $name_property ?>2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $faker = $this->faker();

        $<?= $name_property ?> = <?= $name_class_short ?>::fromString($faker->name());

        $this->assertFalse($<?= $name_property ?>->sameValueAs(FakeVo::create()));
    }
}
