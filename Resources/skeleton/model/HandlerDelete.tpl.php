<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use App\Model\<?= $model; ?>\<?= $list_class; ?>;
use <?= $command_class; ?>;
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

    public function __invoke(<?= $command_class_short; ?> $command): void
    {
        $<?= $model_lower; ?> = $this-><?= $repo_property; ?>->get($command-><?= $id_property; ?>());

        if (!$<?= $model_lower; ?>) {
            throw <?= $model; ?>NotFound::with<?= $id_class_short; ?>($command-><?= $id_property; ?>());
        }

        $<?= $model_lower; ?>->delete();

        $this-><?= $repo_property; ?>->save($<?= $model_lower; ?>);
    }
}
