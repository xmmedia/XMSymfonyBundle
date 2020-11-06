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

        $<?= $id_property; ?> = $faker->uuid;
        $name = $faker->name;

        $entity = new <?= $entity_class_short; ?>();
        $reflection = new \ReflectionClass(<?= $entity_class_short; ?>::class);

        $property = $reflection->getProperty('<?= $id_property; ?>');
        $property->setAccessible(true);
        $property->setValue($entity, Uuid::fromString($<?= $id_property; ?>));

        $property = $reflection->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($entity, $name);

        $this->assertSameValueAs(<?= $id_class; ?>::fromString($<?= $id_property; ?>), $entity-><?= $id_property; ?>());
        $this->assertEquals($name, $entity->name());
    }
}
