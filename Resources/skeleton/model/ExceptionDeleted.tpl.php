<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;

final class <?= $class_name; ?> extends \InvalidArgumentException
{
    public static function triedToChange(<?= $id_class_short; ?> $<?= $id_property; ?>): self
    {
        return new self(
            sprintf(
                'Tried to change the name of <?= $model; ?> with ID "%s" that\'s deleted.',
                $<?= $id_property; ?>,
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
