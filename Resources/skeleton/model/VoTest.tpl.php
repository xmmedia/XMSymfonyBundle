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
        $name = $faker->name;

        $name = Name::fromString($name);

        $this->assertEquals($name, $name->name());
        $this->assertEquals($name, $name->toString());
        $this->assertEquals($name, (string) $name);
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

    public function testSameValueAs(): void
    {
        $faker = $this->faker();
        $name = $faker->name;

        $name1 = Name::fromString($name);
        $name2 = Name::fromString($name);

        $this->assertTrue($name1->sameValueAs($name2));
    }

    public function testSameValueAsFalse(): void
    {
        $faker = $this->faker();

        $name1 = Name::fromString($faker->unique()->name);
        $name2 = Name::fromString($faker->unique()->name);

        $this->assertFalse($name1->sameValueAs($name2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $faker = $this->faker();

        $name = Name::fromString($faker->name);

        $this->assertFalse($name->sameValueAs(FakeVo::create()));
    }
}
