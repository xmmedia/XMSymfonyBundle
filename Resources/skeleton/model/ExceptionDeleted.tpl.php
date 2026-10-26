<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;

final class <?= $class_name; ?> extends \InvalidArgumentException
{
    public static function triedTo(<?= $id_class_short; ?> $<?= $id_property; ?>, string $action): self
    {
        return new self(
            \sprintf(
                'Tried to %s, but <?= $model; ?> with ID "%s" is deleted.',
                $action,
                $<?= $id_property; ?>,
            ),
        );
    }

    public static function triedToDelete(<?= $id_class_short; ?> $<?= $id_property; ?>): self
    {
        return new self(
            \sprintf(
                'Tried to delete <?= $model; ?> with ID "%s" that\'s already deleted.',
                $<?= $id_property; ?>,
            ),
        );
    }
}
