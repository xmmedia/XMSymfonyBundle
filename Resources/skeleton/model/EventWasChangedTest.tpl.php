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

        $<?= $id_property; ?> = $faker-><?= $id_property; ?>();
        $new<?= $name_class_short; ?> = <?= $name_class_short ?>::fromString($faker->name());
        $old<?= $name_class_short; ?> = <?= $name_class_short ?>::fromString($faker->name());

        $event = <?= $event_class_short; ?>::now($<?= $id_property; ?>, $new<?= $name_class_short; ?>, $old<?= $name_class_short; ?>);

        $this->assertSameValueAs($<?= $id_property; ?>, $event-><?= $id_property; ?>());
        $this->assertSameValueAs($new<?= $name_class_short; ?>, $event->new<?= $name_class_short; ?>());
        $this->assertSameValueAs($old<?= $name_class_short; ?>, $event->old<?= $name_class_short; ?>());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker-><?= $id_property; ?>();
        $new<?= $name_class_short; ?> = <?= $name_class_short ?>::fromString($faker->name());
        $old<?= $name_class_short; ?> = <?= $name_class_short ?>::fromString($faker->name());

        /** @var <?= $event_class_short; ?> $event */
        $event = $this->createEventFromArray(
            <?= $event_class_short; ?>::class,
            $<?= $id_property; ?>->toString(),
            [
                'new<?= $name_class_short; ?>' => $new<?= $name_class_short; ?>->toString(),
                'old<?= $name_class_short; ?>' => $old<?= $name_class_short; ?>->toString(),
            ],
        );

        $this->assertInstanceOf(<?= $event_class_short; ?>::class, $event);

        $this->assertSameValueAs($<?= $id_property; ?>, $event-><?= $id_property; ?>());
        $this->assertSameValueAs($new<?= $name_class_short; ?>, $event->new<?= $name_class_short; ?>());
        $this->assertSameValueAs($old<?= $name_class_short; ?>, $event->old<?= $name_class_short; ?>());
    }
}
