<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $resolver_class; ?>;
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

        $resolver = new <?= $resolver_class_short; ?>($finder);

        $result = $resolver();

        $this->assertEquals([$entity], $result);
    }

    public function testNoneFound(): void
    {
        $finder = \Mockery::mock(<?= $finder_class_short; ?>::class);
        $finder->shouldReceive('findAll')
            ->once()
            ->andReturn([]);

        $resolver = new <?= $resolver_class_short; ?>($finder);

        $this->assertEquals([], $resolver());
    }
}
