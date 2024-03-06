<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;

interface <?= $class_name; ?>
{
    public function __invoke(<?= $id_class_short; ?> $<?= $id_property; ?>): bool;
}
