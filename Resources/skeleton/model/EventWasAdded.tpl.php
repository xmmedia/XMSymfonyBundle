<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;
use <?= $name_class; ?>;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class <?= $class_name; ?> extends AggregateChanged
{
    private <?= $name_class_short ?> $<?= $name_property ?>;

    public static function now(
        <?= $id_class_short; ?> $<?= $id_property; ?>,
        <?= $name_class_short ?> $<?= $name_property ?>,
    ): self {
        $event = self::occur($<?= $id_property; ?>->toString(), [
            '<?= $name_property ?>' => $<?= $name_property ?>->toString(),
        ]);

        $event-><?= $name_property ?> = $<?= $name_property ?>;

        return $event;
    }

    public function <?= $id_property; ?>(): <?= $id_class_short; ?><?= "\n"; ?>
    {
        return <?= $id_class_short; ?>::fromString($this->aggregateId());
    }

    public function <?= $name_property ?>(): <?= $name_class_short; ?>
    {
        if (!isset($this-><?= $name_property ?>)) {
            $this-><?= $name_property ?> = <?= $name_class_short ?>::fromString($this->payload['<?= $name_property ?>']);
        }

        return $this-><?= $name_property ?>;
    }
}
