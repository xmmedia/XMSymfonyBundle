<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $command_class; ?>;
use <?= $handler_class; ?>;
use <?= $model_class; ?>;
use <?= $id_class; ?>;
use <?= $name_class; ?>;
use <?= $list_class; ?>;
use <?= $not_found_class; ?>;
use App\Tests\BaseTestCase;

class <?= $class_name; ?> extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $command = <?= $command_class_short; ?>::now($faker-><?= $id_property; ?>(), Name::fromString($faker->name()));

        $<?= $model_lower; ?> = \Mockery::mock(<?= $model; ?>::class);
        $<?= $model_lower; ?>->shouldReceive('change')
            ->once();

        $repo = \Mockery::mock(<?= $list_class_short; ?>::class);
        $repo->shouldReceive('get')
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->andReturn($<?= $model_lower; ?>);
        $repo->shouldReceive('save')
            ->once()
            ->with(\Mockery::type(<?= $model; ?>::class));

        (new <?= $handler_class_short; ?>($repo))($command);
    }

    public function testNotFound(): void
    {
        $faker = $this->faker();

        $command = <?= $command_class_short; ?>::now($faker-><?= $id_property; ?>(), Name::fromString($faker->name()));

        $repo = \Mockery::mock(<?= $list_class_short; ?>::class);
        $repo->shouldReceive('get')
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->andReturnNull();

        $this->expectException(<?= $not_found_class_short; ?>::class);

        (new <?= $handler_class_short; ?>($repo))($command);
    }
}
