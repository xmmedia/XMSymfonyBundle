<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?php if (!$edit) { ?>
use App\Model\<?= $model; ?>\<?= $model; ?>;
<?php } ?>
use <?= $list_class; ?>;
use <?= $command_class; ?>;
<?php if ($edit) { ?>
use App\Model\<?= $model; ?>\Exception\<?= $model; ?>NotFound;
<?php } ?>

final readonly class <?= $class_name; ?><?= "\n"; ?>
{
    public function __construct(private <?= $list_class_short; ?> $<?= $repo_property; ?>)
    {
    }

    public function __invoke(<?= $command_class_short; ?> $command): void
    {
<?php if ($edit) { ?>
        $<?= $model_lower; ?> = $this-><?= $repo_property; ?>->get($command-><?= $id_property; ?>());

        if (!$<?= $model_lower; ?>) {
            throw <?= $model; ?>NotFound::with<?= $id_class_short; ?>($command-><?= $id_property; ?>());
        }

        $<?= $model_lower; ?>->change($command->name());
<?php } else { ?>
        $<?= $model_lower; ?> = <?= $model; ?>::add(
            $command-><?= $id_property; ?>(),
            $command->name(),
        );
<?php } ?>

        $this-><?= $repo_property; ?>->save($<?= $model_lower; ?>);
    }
}
