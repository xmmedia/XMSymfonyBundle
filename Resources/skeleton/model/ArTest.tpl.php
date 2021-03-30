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
    public function testAdd(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker-><?= $id_property; ?>();
        $name = Name::fromString($faker->name());

        $<?= $model_lower; ?> = <?= $model; ?>::add(
            $<?= $id_property; ?>,
            $name,
        );

        $this->assertInstanceOf(<?= $model; ?>::class, $<?= $model_lower; ?>);

        $events = $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->assertRecordedEvent(
            Event\<?= $model; ?>WasAdded::class,
            [
                'name' => $name->toString(),
            ],
            $events,
        );

        $this->assertCount(1, $events);

        $this->assertSameValueAs($<?= $id_property; ?>, $<?= $model_lower; ?>-><?= $id_property; ?>());
    }

    public function testChangeName(): void
    {
        $faker = $this->faker();

        $newName = Name::fromString($faker->unique()->name());

        $<?= $model_lower; ?> = $this->get<?= $model; ?>();
        $oldName = $<?= $model_lower; ?>->name();

        $<?= $model_lower; ?>->changeName($newName);

        $events = $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->assertRecordedEvent(
            Event\<?= $model; ?>NameWasChanged::class,
            [
                'newName' => $newName->toString(),
                'oldName' => $oldName->toString(),
            ],
            $events,
        );

        $this->assertCount(1, $events);

        $this->assertSameValueAs($newName, $<?= $model_lower; ?>->name());
    }

    public function testChangeNameNoChange(): void
    {
        $<?= $model_lower; ?> = $this->get<?= $model; ?>();

        $name = $<?= $model_lower; ?>->name();

        $<?= $model_lower; ?>->changeName($name);

        $events = $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->assertCount(0, $events);

        $this->assertSameValueAs($name, $<?= $model_lower; ?>->name());
    }

    public function testChangeNameDeleted(): void
    {
        $faker = $this->faker();

        $<?= $model_lower; ?> = $this->get<?= $model; ?>();
        $<?= $model_lower; ?>->delete();
        $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->expectException(Exception\<?= $model; ?>IsDeleted::class);

        $<?= $model_lower; ?>->changeName(Name::fromString($faker->name()));
    }

    public function testDelete(): void
    {
        $<?= $model_lower; ?> = $this->get<?= $model; ?>();

        $<?= $model_lower; ?>->delete();

        $events = $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->assertRecordedEvent(
            Event\<?= $model; ?>WasDeleted::class,
            [],
            $events,
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

        $<?= $id_property; ?> = $faker-><?= $id_property; ?>();

        $<?= $model_lower; ?>1 = <?= $model; ?>::add(
            $<?= $id_property; ?>,
            Name::fromString($faker->name()),
        );
        $<?= $model_lower; ?>2 = <?= $model; ?>::add(
            $<?= $id_property; ?>,
            Name::fromString($faker->name()),
        );

        $this->assertTrue($<?= $model_lower; ?>1->sameIdentityAs($<?= $model_lower; ?>2));
    }

    public function testSameIdentityAsFalse(): void
    {
        $faker = $this->faker();

        $<?= $model_lower; ?>1 = <?= $model; ?>::add(
            $<?= $id_property; ?>,
            Name::fromString($faker->name()),
        );
        $<?= $model_lower; ?>2 = <?= $model; ?>::add(
            $<?= $id_property; ?>,
            Name::fromString($faker->name()),
        );

        $this->assertFalse($<?= $model_lower; ?>1->sameIdentityAs($<?= $model_lower; ?>2));
    }

    public function testSameIdentityAsDiffClass(): void
    {
        $faker = $this->faker();

        $<?= $model_lower; ?> = <?= $model; ?>::add(
            $faker-><?= $id_property; ?>(),
            Name::fromString($faker->name()),
        );

        $this->assertFalse($<?= $model_lower; ?>->sameIdentityAs(FakeAr::add()));
    }

    private function get<?= $model; ?>(): <?= $model; ?><?= "\n"; ?>
    {
        $faker = $this->faker();

        $<?= $model_lower; ?> = <?= $model; ?>::add(
            $faker-><?= $id_property; ?>(),
            Name::fromString($faker->unique()->name()),
        );
        $this->popRecordedEvent($<?= $model_lower; ?>);

        return $<?= $model_lower; ?>;
    }
}
