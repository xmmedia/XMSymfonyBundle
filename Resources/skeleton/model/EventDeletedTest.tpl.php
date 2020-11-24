<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $event_class; ?>;
use App\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class <?= $class_name; ?> extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker-><?= $id_property; ?>;

        $event = <?= $event_class_short; ?>::now($<?= $id_property; ?>);

        $this->assertSameValueAs($<?= $id_property; ?>, $event-><?= $id_property; ?>());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker-><?= $id_property; ?>;

        /** @var <?= $event_class_short; ?> $event */
        $event = $this->createEventFromArray(
            <?= $event_class_short; ?>::class,
            $<?= $id_property; ?>->toString(),
            [],
        );

        $this->assertInstanceOf(<?= $event_class_short; ?>::class, $event);

        $this->assertSameValueAs($<?= $id_property; ?>, $event-><?= $id_property; ?>());
    }
}
