<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $event_class; ?>;
use <?= $name_class; ?>;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class <?= $class_name; ?> extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker-><?= $id_property; ?>;
        $newName = Name::fromString($faker->name);
        $oldName = Name::fromString($faker->name);

        $event = <?= $event_class_short; ?>::now($<?= $id_property; ?>, $newName, $oldName);

        $this->assertSameValueAs($<?= $id_property; ?>, $event-><?= $id_property; ?>());
        $this->assertSameValueAs($newName, $event->newName());
        $this->assertSameValueAs($oldName, $event->oldName());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker-><?= $id_property; ?>;
        $newName = Name::fromString($faker->name);
        $oldName = Name::fromString($faker->name);

        /** @var <?= $event_class_short; ?> $event */
        $event = $this->createEventFromArray(
            <?= $event_class_short; ?>::class,
            $<?= $id_property; ?>->toString(),
            [
                'newName' => $newName->toString(),
                'oldName' => $oldName->toString(),
            ],
        );

        $this->assertInstanceOf(<?= $event_class_short; ?>::class, $event);

        $this->assertSameValueAs($<?= $id_property; ?>, $event-><?= $id_property; ?>());
        $this->assertSameValueAs($newName, $event->newName());
        $this->assertSameValueAs($oldName, $event->oldName());
    }
}
