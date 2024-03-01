<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $command_class; ?>;
use <?= $handler_class; ?>;
use <?= $model_class; ?>;
use <?= $name_class; ?>;
use <?= $list_class; ?>;
use App\Tests\BaseTestCase;

class <?= $class_name; ?> extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $command = <?= $command_class_short; ?>::now(
            $faker-><?= $id_property; ?>(),
            <?= $name_class_short ?>::fromString($faker->name()),
        );

        $repo = \Mockery::mock(<?= $list_class_short; ?>::class);
        $repo->shouldReceive('save')
            ->once()
            ->with(\Mockery::type(<?= $model; ?>::class));

        (new <?= $handler_class_short; ?>($repo))($command);
    }
}
