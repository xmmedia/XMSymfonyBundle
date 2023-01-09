<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $resolver_class; ?>;
use <?= $id_class; ?>;
use <?= $finder_class; ?>;
use App\Tests\BaseTestCase;

class <?= $class_name; ?> extends BaseTestCase
{
    public function testFound(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker->uuid();
        $entity = \Mockery::mock(<?= $entity_class_short; ?>::class);

        $finder = \Mockery::mock(<?= $finder_class_short; ?>::class);
        $finder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->andReturn($entity);

        $resolver = new <?= $resolver_class_short; ?>($finder);

        $result = $resolver($<?= $id_property; ?>);

        $this->assertEquals($entity, $result);
    }

    public function testNotFound(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker->uuid();

        $finder = \Mockery::mock(<?= $finder_class_short; ?>::class);
        $finder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->andReturnNull();

        $resolver = new <?= $resolver_class_short; ?>($finder);

        $result = $resolver($<?= $id_property; ?>);

        $this->assertNull($result);
    }
}
