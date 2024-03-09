<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $command_class; ?>;
use <?= $handler_class; ?>;
use <?= $model_class; ?>;
use <?= $id_class; ?>;
use <?= $list_class; ?>;
use <?= $not_found_class; ?>;
use <?= $can_be_deleted_interface_class; ?>;
use App\Tests\BaseTestCase;

class <?= $class_name; ?> extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $command = <?= $command_class_short; ?>::now($faker-><?= $id_property; ?>());

        $<?= $model_lower; ?> = \Mockery::mock(<?= $model; ?>::class);
        $<?= $model_lower; ?>->shouldReceive('delete')
            ->once();

        $repo = \Mockery::mock(<?= $list_class_short; ?>::class);
        $repo->shouldReceive('get')
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->andReturn($<?= $model_lower; ?>);
        $repo->shouldReceive('save')
            ->once()
            ->with(\Mockery::type(<?= $model; ?>::class));

        $canBeDeleted = \Mockery::mock(<?= $can_be_deleted_interface_class_short; ?>::class);
        $canBeDeleted->shouldReceive('__invoke')
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->andReturnTrue();

        (new <?= $handler_class_short; ?>($repo, $canBeDeleted))($command);
    }

    public function testNotFound(): void
    {
        $faker = $this->faker();

        $command = <?= $command_class_short; ?>::now($faker-><?= $id_property; ?>());

        $repo = \Mockery::mock(<?= $list_class_short; ?>::class);
        $repo->shouldReceive('get')
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->andReturnNull();

        $canBeDeleted = \Mockery::mock(<?= $can_be_deleted_interface_class_short; ?>::class);
        $canBeDeleted->shouldReceive('__invoke')
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->andReturnTrue();

        $this->expectException(<?= $not_found_class_short; ?>::class);

        (new <?= $handler_class_short; ?>($repo, $canBeDeleted))($command);
    }
}
