<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;

class <?= $class_name; ?> implements \<?= $can_be_deleted_interface_class; ?>
{
    public function __invoke(<?= $id_class_short; ?> $<?= $id_property; ?>): bool
    {
        return true;
    }
}
