<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $id_class; ?>;
use App\Tests\BaseTestCase;
use Ramsey\Uuid\Uuid;

class <?= $class_name; ?> extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker->uuid();
        $<?= $name_property; ?> = $faker->name();

        $entity = new <?= $entity_class_short; ?>();
        $reflection = new \ReflectionClass(<?= $entity_class_short; ?>::class);

        $reflection->getProperty('<?= $id_property; ?>')
            ->setValue($entity, Uuid::fromString($<?= $id_property; ?>));
        $reflection->getProperty('<?= $name_property; ?>')
            ->setValue($entity, $<?= $name_property; ?>);

        $this->assertSameValueAs(<?= $id_class_short; ?>::fromString($<?= $id_property; ?>), $entity-><?= $id_property; ?>());
        $this->assertSame($<?= $name_property; ?>, $entity-><?= $name_property; ?>());
    }
}
