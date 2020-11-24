<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $repository_class; ?>;
use App\Model\<?= $model; ?>\<?= $model; ?>;
use App\Tests\BaseTestCase;

class <?= $class_name; ?> extends BaseTestCase
{
    use AggregateRepositoryFactory;

    public function testSaveGet(): void
    {
        $faker = $this->faker();

        $<?= $model_lower; ?> = <?= $model; ?>::create(
            $faker-><?= $id_property; ?>,
            $faker->name,
        );

        $repository = $this->getRepository(<?= $repository_class_short; ?>::class, <?= $model; ?>::class);

        $repository->save($<?= $model_lower; ?>);

        $fetched = $repository->get($<?= $model_lower; ?>-><?= $id_property; ?>());

        $this->assertInstanceOf(<?= $model; ?>::class, $fetched);
        $this->assertNotSame($<?= $model_lower; ?>, $fetched);
        $this->assertSameValueAs($<?= $model_lower; ?>-><?= $id_property; ?>(), $fetched-><?= $id_property; ?>());
    }

    public function testGetDoesntExist(): void
    {
        $faker = $this->faker();

        $fetched = $this->getRepository(<?= $repository_class_short; ?>::class, <?= $model; ?>::class)
            ->get($faker-><?= $id_property; ?>);

        $this->assertNull($fetched);
    }
}
