<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $not_found_class; ?>;
use <?= $entity_finder_class; ?>;
use App\Tests\BaseTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class <?= $class_name; ?> extends BaseTestCase
{
    public function testFindOrThrow(): void
    {
        $<?= $id_property; ?> = $this->faker()-><?= $id_property; ?>();
        $<?= $entity; ?> = \Mockery::mock(<?= $entity_class_short; ?>::class);

        $finder = $this->finder();
        $finder->shouldReceive('find')
            ->once()
            ->with($<?= $id_property; ?>)
            ->andReturn($<?= $entity; ?>);

        $this->assertSame($<?= $entity; ?>, $finder->findOrThrow($<?= $id_property; ?>));
    }

    public function testFindOrThrowNotFound(): void
    {
        $<?= $id_property; ?> = $this->faker()-><?= $id_property; ?>();

        $finder = $this->finder();
        $finder->shouldReceive('find')
            ->once()
            ->with($<?= $id_property; ?>)
            ->andReturnNull();

        $this->expectException(<?= $not_found_class_short; ?>::class);

        $finder->findOrThrow($<?= $id_property; ?>);
    }

    public function testFindRefreshed(): void
    {
        $<?= $id_property; ?> = $this->faker()-><?= $id_property; ?>();
        $<?= $entity; ?> = \Mockery::mock(<?= $entity_class_short; ?>::class);

        $entityManager = \Mockery::mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('refresh')
            ->once()
            ->with($<?= $entity; ?>);

        $finder = $this->finder();
        $finder->shouldReceive('find')
            ->once()
            ->with($<?= $id_property; ?>)
            ->andReturn($<?= $entity; ?>);
        $finder->shouldReceive('getEntityManager')
            ->once()
            ->andReturn($entityManager);

        $this->assertSame($<?= $entity; ?>, $finder->findRefreshed($<?= $id_property; ?>));
    }

    public function testFindRefreshedNotFound(): void
    {
        $<?= $id_property; ?> = $this->faker()-><?= $id_property; ?>();

        $finder = $this->finder();
        $finder->shouldReceive('find')
            ->once()
            ->with($<?= $id_property; ?>)
            ->andReturnNull();
        $finder->shouldNotReceive('getEntityManager');

        $this->assertNull($finder->findRefreshed($<?= $id_property; ?>));
    }

    private function finder(): <?= $entity_filter_class_short; ?>&\Mockery\MockInterface
    {
        return \Mockery::mock(<?= $entity_filter_class_short; ?>::class, [\Mockery::mock(ManagerRegistry::class)])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }
}
