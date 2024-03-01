<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use App\Model\<?= $model; ?>\<?= $model; ?>;
use <?= $id_class; ?>;
use <?= $list_class; ?>;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRepository;

final class <?= $class_name; ?> extends AggregateRepository implements <?= $list_class_short; ?><?= "\n"; ?>
{
    public function save(<?= $model; ?> $<?= $model_lower; ?>): void
    {
        $this->saveAggregateRoot($<?= $model_lower; ?>);
    }

    public function get(<?= $id_class_short; ?> $<?= $id_property; ?>): ?<?= $model; ?><?= "\n"; ?>
    {
        return $this->getAggregateRoot($<?= $id_property; ?>->toString());
    }
}
