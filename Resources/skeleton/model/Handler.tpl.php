<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

<?php if (!$edit) { ?>
use App\Model\<?= $model; ?>\<?= $model; ?>;
<?php } ?>
use App\Model\<?= $model; ?>\<?= $list_class; ?>;
use <?= $command_full_class; ?>;
<?php if ($edit) { ?>
use App\Model\<?= $model; ?>\Exception\<?= $model; ?>NotFound;
<?php } ?>

final class <?= $class_name; ?><?= "\n"; ?>
{
    /** @var <?= $list_class; ?> */
    private $<?= $repo_property; ?>;

    public function __construct(<?= $list_class; ?> $<?= $repo_property; ?>)
    {
        $this-><?= $repo_property; ?> = $<?= $repo_property; ?>;
    }

    public function __invoke(<?= $command_class; ?> $command): void
    {
<?php if ($edit) { ?>
        $<?= $model_lower; ?> = $this-><?= $repo_property; ?>->get($command-><?= $id_property; ?>());

        if (!$<?= $model_lower; ?>) {
            throw <?= $model; ?>NotFound::with<?= $id_class_short; ?>($command-><?= $id_property; ?>());
        }

        $<?= $model_lower; ?>->update($command->name());
<?php } else { ?>
        $<?= $model_lower; ?> = <?= $model; ?>::create(
            $command-><?= $id_property; ?>(),
            $command->name(),
        );
<?php } ?>

        $this-><?= $repo_property; ?>->save($<?= $model_lower; ?>);
    }
}
