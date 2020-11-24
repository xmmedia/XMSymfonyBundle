<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;

final class <?= $class_name; ?> extends \InvalidArgumentException
{
    public static function with<?= $id_class_short; ?>(<?= $id_class_short; ?> $<?= $id_property; ?>): self
    {
        return new self(
            sprintf(
                '<?= $model; ?> with ID "%s" cannot be found.',
                $<?= $id_property; ?>,
            ),
        );
    }
}
