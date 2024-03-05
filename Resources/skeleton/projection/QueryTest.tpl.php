<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $query_single_class; ?>;
use <?= $id_class; ?>;
use <?= $entity_finder_class; ?>;
use App\Tests\BaseTestCase;

class <?= $class_name; ?> extends BaseTestCase
{
    public function testFound(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker->uuid();
        $entity = \Mockery::mock(<?= $entity_class_short; ?>::class);

        $finder = \Mockery::mock(<?= $entity_filter_class_short; ?>::class);
        $finder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->andReturn($entity);

        $query = new <?= $query_single_class_short; ?>($finder);

        $result = $query($<?= $id_property; ?>);

        $this->assertSame($entity, $result);
    }

    public function testNotFound(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker->uuid();

        $finder = \Mockery::mock(<?= $entity_filter_class_short; ?>::class);
        $finder->shouldReceive('find')
            ->once()
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->andReturnNull();

        $query = new <?= $query_single_class_short; ?>($finder);

        $result = $query($<?= $id_property; ?>);

        $this->assertNull($result);
    }
}
