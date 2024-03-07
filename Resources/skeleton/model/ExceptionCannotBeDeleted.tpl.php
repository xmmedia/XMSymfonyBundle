<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;

final class <?= $class_name; ?> extends \InvalidArgumentException
{
    public static function triedToDelete(<?= $id_class_short; ?> $<?= $id_property; ?>): self
    {
        return new self(
            sprintf(
                'Tried to delete an <?= $model; ?> with ID "%s" that cannot be deleted.',
                $<?= $id_property; ?>,
            ),
        );
    }
}
