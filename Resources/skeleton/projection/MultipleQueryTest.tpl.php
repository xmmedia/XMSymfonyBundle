<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $query_class; ?>;
use <?= $finder_class; ?>;
use App\Tests\BaseTestCase;

class <?= $class_name; ?> extends BaseTestCase
{
    public function test(): void
    {
        $entity = \Mockery::mock(<?= $entity_class_short; ?>::class);

        $finder = \Mockery::mock(<?= $finder_class_short; ?>::class);
        $finder->shouldReceive('findAll')
            ->once()
            ->andReturn([$entity]);

        $query = new <?= $query_class_short; ?>($finder);

        $result = $query();

        $this->assertSame([$entity], $result);
    }

    public function testNoneFound(): void
    {
        $finder = \Mockery::mock(<?= $finder_class_short; ?>::class);
        $finder->shouldReceive('findAll')
            ->once()
            ->andReturn([]);

        $query = new <?= $query_class_short; ?>($finder);

        $this->assertSame([], $query());
    }
}
