<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use App\Model\<?= $model; ?>\Event;
use App\Model\<?= $model; ?>\Exception;
use <?= $name_class; ?>;
use <?= $model_class; ?>;
use App\Tests\BaseTestCase;
use Mockery;
use Xm\SymfonyBundle\Tests\FakeAr;

class <?= $class_name; ?> extends BaseTestCase
{
    public function testCreate(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker-><?= $id_property; ?>;
        $name = Name::fromString($faker->name);

        $<?= $model_lower; ?> = <?= $model; ?>::create(
            $<?= $id_property; ?>,
            $name
        );

        $this->assertInstanceOf(<?= $model; ?>::class, $<?= $model_lower; ?>);

        $events = $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->assertRecordedEvent(
            Event\<?= $model; ?>WasAdded::class,
            [
                'name' => $name->toString(),
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertSameValueAs($<?= $id_property; ?>, $<?= $model_lower; ?>-><?= $id_property; ?>());
    }

    public function testUpdate(): void
    {
        $faker = $this->faker();

        $name = Name::fromString($faker->name);

        $<?= $model_lower; ?> = $this->get<?= $model; ?>();

        $<?= $model_lower; ?>->update($name);

        $events = $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->assertRecordedEvent(
            Event\<?= $model; ?>WasUpdated::class,
            [
                'name' => $name->toString(),
            ],
            $events
        );

        $this->assertCount(1, $events);

        $this->assertSameValueAs($name, $<?= $model_lower; ?>->name());
    }

    public function testUpdateDeleted(): void
    {
        $faker = $this->faker();

        $name = Name::fromString($faker->name);

        $<?= $model_lower; ?> = $this->get<?= $model; ?>();
        $<?= $model_lower; ?>->delete();
        $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->expectException(Exception\<?= $model; ?>IsDeleted::class);

        $<?= $model_lower; ?>->update($name);
    }

    public function testDelete(): void
    {
        $<?= $model_lower; ?> = $this->get<?= $model; ?>();

        $<?= $model_lower; ?>->delete();

        $events = $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->assertRecordedEvent(
            Event\<?= $model; ?>WasDeleted::class,
            [],
            $events
        );

        $this->assertCount(1, $events);
    }

    public function testDeleteAlreadyDeleted(): void
    {
        $<?= $model_lower; ?> = $this->get<?= $model; ?>();

        $<?= $model_lower; ?>->delete();
        $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->expectException(Exception\<?= $model; ?>IsDeleted::class);

        $<?= $model_lower; ?>->delete();
    }

    public function testSameIdentityAs(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker-><?= $id_property; ?>;
        $name = Name::fromString($faker->name);

        $<?= $model_lower; ?>1 = <?= $model; ?>::create($<?= $id_property; ?>, $name);
        $<?= $model_lower; ?>2 = <?= $model; ?>::create($<?= $id_property; ?>, $name);

        $this->assertTrue($<?= $model_lower; ?>1->sameIdentityAs($<?= $model_lower; ?>2));
    }

    public function testSameIdentityAsFalse(): void
    {
        $faker = $this->faker();

        $name = Name::fromString($faker->name);

        $<?= $model_lower; ?>1 = <?= $model; ?>::create($faker-><?= $id_property; ?>, $name);
        $<?= $model_lower; ?>2 = <?= $model; ?>::create($faker-><?= $id_property; ?>, $name);

        $this->assertFalse($<?= $model_lower; ?>1->sameIdentityAs($<?= $model_lower; ?>2));
    }

    public function testSameIdentityAsDiffClass(): void
    {
        $faker = $this->faker();

        $<?= $model_lower; ?> = <?= $model; ?>::create(
            $faker-><?= $id_property; ?>,
            Name::fromString($faker->name)
        );

        $this->assertFalse($<?= $model_lower; ?>->sameIdentityAs(FakeAr::create()));
    }

    private function get<?= $model; ?>(): <?= $model; ?><?= "\n"; ?>
    {
        $faker = $this->faker();

        $<?= $model_lower; ?> = <?= $model; ?>::create(
            $faker-><?= $id_property; ?>,
            Name::fromString($faker->name)
        );
        $this->popRecordedEvent($<?= $model_lower; ?>);

        return $<?= $model_lower; ?>;
    }
}
