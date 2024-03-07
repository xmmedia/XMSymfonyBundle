<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use App\Model\<?= $model; ?>\Event;
use App\Model\<?= $model; ?>\Exception;
use <?= $name_class; ?>;
use <?= $model_class; ?>;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeAr;

class <?= $class_name; ?> extends BaseTestCase
{
    public function testAdd(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker-><?= $id_property; ?>();
        $<?= $name_property; ?> = <?= $name_class_short; ?>::fromString($faker->name());

        $<?= $model_lower; ?> = <?= $model; ?>::add($<?= $id_property; ?>, $<?= $name_property; ?>);

        $this->assertInstanceOf(<?= $model; ?>::class, $<?= $model_lower; ?>);

        $events = $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->assertRecordedEvent(
            Event\<?= $model; ?>WasAdded::class,
            [
                '<?= $name_property; ?>' => $<?= $name_property; ?>->toString(),
            ],
            $events,
        );

        $this->assertCount(1, $events);

        $this->assertSameValueAs($<?= $id_property; ?>, $<?= $model_lower; ?>-><?= $id_property; ?>());
    }

    public function testChange(): void
    {
        $faker = $this->faker();

        $new<?= $name_class_short; ?> = <?= $name_class_short; ?>::fromString($faker->unique()->name());

        $<?= $model_lower; ?> = $this->get<?= $model; ?>();
        $old<?= $name_class_short; ?> = $<?= $model_lower; ?>-><?= $name_property; ?>();

        $<?= $model_lower; ?>->change($new<?= $name_class_short; ?>);

        $events = $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->assertRecordedEvent(
            Event\<?= $model; ?>WasChanged::class,
            [
                'new<?= $name_class_short; ?>' => $new<?= $name_class_short; ?>->toString(),
                'old<?= $name_class_short; ?>' => $old<?= $name_class_short; ?>->toString(),
            ],
            $events,
        );

        $this->assertCount(1, $events);

        $this->assertSameValueAs($new<?= $name_class_short; ?>, $<?= $model_lower; ?>-><?= $name_property; ?>());
    }

    public function testChangeNoChange(): void
    {
        $<?= $model_lower; ?> = $this->get<?= $model; ?>();

        $<?= $name_property; ?> = $<?= $model_lower; ?>-><?= $name_property; ?>();

        $<?= $model_lower; ?>->change($<?= $name_property; ?>);

        $events = $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->assertCount(0, $events);

        $this->assertSameValueAs($<?= $name_property; ?>, $<?= $model_lower; ?>-><?= $name_property; ?>());
    }

    public function testChangeDeleted(): void
    {
        $faker = $this->faker();

        $<?= $model_lower; ?> = $this->get<?= $model; ?>();

        $canBeDeleted = \Mockery::mock(<?= $can_be_deleted_interface_class; ?>::class);
        $canBeDeleted->shouldReceive('__invoke')
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->andReturnTrue();

        $<?= $model_lower; ?>->delete($canBeDeleted);
        $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->expectException(Exception\<?= $model; ?>IsDeleted::class);

        $<?= $model_lower; ?>->change(<?= $name_class_short; ?>::fromString($faker->unique()->name()));
    }

    public function testDelete(): void
    {
        $<?= $model_lower; ?> = $this->get<?= $model; ?>();

        $canBeDeleted = \Mockery::mock(<?= $can_be_deleted_interface_class; ?>::class);
        $canBeDeleted->shouldReceive('__invoke')
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->once()
            ->andReturnTrue();

        $<?= $model_lower; ?>->delete($canBeDeleted);

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

        $canBeDeleted = \Mockery::mock(<?= $can_be_deleted_interface_class; ?>::class);
        $canBeDeleted->shouldReceive('__invoke')
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->andReturnTrue();

        $<?= $model_lower; ?>->delete($canBeDeleted);
        $this->popRecordedEvent($<?= $model_lower; ?>);

        $this->expectException(Exception\<?= $model; ?>IsDeleted::class);

        $<?= $model_lower; ?>->delete($canBeDeleted);
    }

    public function testDeleteCannotBeDeleted(): void
    {
        $<?= $model_lower; ?> = $this->get<?= $model; ?>();

        $canBeDeleted = \Mockery::mock(<?= $can_be_deleted_interface_class; ?>::class);
        $canBeDeleted->shouldReceive('__invoke')
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->once()
            ->andReturnFalse();

        $this->expectException(Exception\<?= $model; ?>CannotBeDeleted::class);

        $<?= $model_lower; ?>->delete($canBeDeleted);
    }

    public function testSameIdentityAs(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker-><?= $id_property; ?>();

        $<?= $model_lower; ?>1 = <?= $model; ?>::add(
            $<?= $id_property; ?>,
            <?= $name_class_short; ?>::fromString($faker->name()),
        );
        $<?= $model_lower; ?>2 = <?= $model; ?>::add(
            $<?= $id_property; ?>,
            <?= $name_class_short; ?>::fromString($faker->name()),
        );

        $this->assertTrue($<?= $model_lower; ?>1->sameIdentityAs($<?= $model_lower; ?>2));
    }

    public function testSameIdentityAsFalse(): void
    {
        $faker = $this->faker();

        $<?= $model_lower; ?>1 = <?= $model; ?>::add(
            $faker-><?= $id_property; ?>(),
            <?= $name_class_short; ?>::fromString($faker->name()),
        );
        $<?= $model_lower; ?>2 = <?= $model; ?>::add(
            $faker-><?= $id_property; ?>(),
            <?= $name_class_short; ?>::fromString($faker->name()),
        );

        $this->assertFalse($<?= $model_lower; ?>1->sameIdentityAs($<?= $model_lower; ?>2));
    }

    public function testSameIdentityAsDiffClass(): void
    {
        $faker = $this->faker();

        $<?= $model_lower; ?> = <?= $model; ?>::add(
            $faker-><?= $id_property; ?>(),
            <?= $name_class_short; ?>::fromString($faker->name()),
        );

        $this->assertFalse($<?= $model_lower; ?>->sameIdentityAs(FakeAr::create()));
    }

    private function get<?= $model; ?>(): <?= $model; ?><?= "\n"; ?>
    {
        $faker = $this->faker();

        $<?= $model_lower; ?> = <?= $model; ?>::add(
            $faker-><?= $id_property; ?>(),
            <?= $name_class_short; ?>::fromString($faker->unique()->name()),
        );
        $this->popRecordedEvent($<?= $model_lower; ?>);

        return $<?= $model_lower; ?>;
    }
}
