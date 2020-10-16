<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

interface <?= $class_name; ?><?= "\n"; ?>
{
    public function save(<?= $model; ?> $<?= $model_lower; ?>): void;

    public function get(<?= $id_class; ?> $<?= $model_lower; ?>Id): ?<?= $model; ?>;
}
