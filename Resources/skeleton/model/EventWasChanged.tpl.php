<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;
use <?= $name_class; ?>;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class <?= $class_name; ?> extends AggregateChanged
{
    private <?= $name_class_short; ?> $new<?= $name_class_short; ?>;
    private <?= $name_class_short; ?> $old<?= $name_class_short; ?>;

    public static function now(
        <?= $id_class_short; ?> $<?= $id_property; ?>,
        <?= $name_class_short; ?> $new<?= $name_class_short; ?>,
        <?= $name_class_short; ?> $old<?= $name_class_short; ?>,
    ): self {
        $event = self::occur($<?= $id_property; ?>->toString(), [
            'new<?= $name_class_short; ?>' => $new<?= $name_class_short; ?>->toString(),
            'old<?= $name_class_short; ?>' => $old<?= $name_class_short; ?>->toString(),
        ]);

        $event->new<?= $name_class_short; ?> = $new<?= $name_class_short; ?>;
        $event->old<?= $name_class_short; ?> = $old<?= $name_class_short; ?>;

        return $event;
    }

    public function <?= $id_property; ?>(): <?= $id_class_short; ?><?= "\n"; ?>
    {
        return <?= $id_class_short; ?>::fromString($this->aggregateId());
    }

    public function new<?= $name_class_short; ?>(): <?= $name_class_short; ?><?= "\n"; ?>
    {
        if (!isset($this->new<?= $name_class_short; ?>)) {
            $this->new<?= $name_class_short; ?> = <?= $name_class_short ?>::fromString($this->payload['new<?= $name_class_short; ?>']);
        }

        return $this->new<?= $name_class_short; ?>;
    }

    public function old<?= $name_class_short; ?>(): <?= $name_class_short; ?><?= "\n"; ?>
    {
        if (!isset($this->old<?= $name_class_short; ?>)) {
            $this->old<?= $name_class_short; ?> = <?= $name_class_short ?>::fromString($this->payload['old<?= $name_class_short; ?>']);
        }

        return $this->old<?= $name_class_short; ?>;
    }
}
