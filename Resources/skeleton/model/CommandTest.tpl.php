<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $command_class; ?>;
use <?= $name_class; ?>;
use App\Tests\BaseTestCase;

class <?= $class_name; ?> extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker-><?= $id_property; ?>();
        $<?= $name_property ?> = <?= $name_class_short ?>::fromString($faker->name());

        $command = <?= $command_class_short; ?>::now($<?= $id_property; ?>, $<?= $name_property ?>);

        $this->assertSameValueAs($<?= $id_property; ?>, $command-><?= $id_property; ?>());
        $this->assertSameValueAs($<?= $name_property ?>, $command-><?= $name_property ?>());
    }
}
