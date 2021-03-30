<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $command_class; ?>;
use App\Tests\BaseTestCase;

class <?= $class_name; ?> extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $<?= $id_property; ?> = $faker-><?= $id_property; ?>();

        $command = <?= $command_class_short; ?>::now($<?= $id_property; ?>);

        $this->assertSameValueAs($<?= $id_property; ?>, $command-><?= $id_property; ?>());
    }
}
