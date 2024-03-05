<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $query_multiple_class; ?>;
use <?= $entity_finder_class; ?>;
use App\Tests\BaseTestCase;

class <?= $class_name; ?> extends BaseTestCase
{
    public function test(): void
    {
        $entity = \Mockery::mock(<?= $entity_class_short; ?>::class);

        $finder = \Mockery::mock(<?= $entity_filter_class_short; ?>::class);
        $finder->shouldReceive('findByFilters')
            ->once()
            ->andReturn([$entity]);

        $query = new <?= $query_multiple_class_short; ?>($finder);

        $result = $query([]);

        $this->assertSame([$entity], $result);
    }

    public function testNoneFound(): void
    {
        $finder = \Mockery::mock(<?= $entity_filter_class_short; ?>::class);
        $finder->shouldReceive('findByFilters')
            ->once()
            ->andReturn([]);

        $query = new <?= $query_multiple_class_short; ?>($finder);

        $this->assertSame([], $query([]));
    }
}
