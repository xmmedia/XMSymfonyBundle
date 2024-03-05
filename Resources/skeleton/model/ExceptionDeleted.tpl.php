<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;

final class <?= $class_name; ?> extends \InvalidArgumentException
{
    public static function triedTo(<?= $id_class_short; ?> $<?= $id_property; ?>, string $action): self
    {
        return new self(
            sprintf(
                'Tried to %s of <?= $model; ?> with ID "%s" that\'s deleted.',
                $<?= $id_property; ?>,
                $action,
            ),
        );
    }

    public static function triedToDelete(<?= $id_class_short; ?> $<?= $id_property; ?>): self
    {
        return new self(
            sprintf(
                'Tried to delete an <?= $model; ?> with ID "%s" that\'s already deleted.',
                $<?= $id_property; ?>,
            ),
        );
    }
}
