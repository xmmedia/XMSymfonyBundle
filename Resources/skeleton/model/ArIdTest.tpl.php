<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;
use App\Tests\BaseTestCase;

class <?= $class_name; ?> extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();

        $uuid = $faker->uuid();

        $<?= $id_property; ?> = <?= $id_class_short; ?>::fromString($uuid);

        $this->assertSame($uuid, $<?= $id_property; ?>->toString());
    }
}
