<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use App\Tests\BaseTestCase;

class <?= $class_name; ?> extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $name = $faker->name;

        $entity = new <?= $entity_class_short; ?>();
        $reflection = new \ReflectionClass(<?= $entity_class_short; ?>::class);

        $property = $reflection->getProperty('name');
        $property->setAccessible(true);
        $property->setValue($entity, $name);

        $this->assertEquals($name, $entity->name());
    }
}
