<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;
use <?= $name_class; ?>;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

final class <?= $class_name; ?> extends AggregateChanged
{
    private <?= $has_readonly_properties ? 'readonly ' : ''; ?>Name $name;

    public static function now(
        <?= $id_class_short; ?> $<?= $id_property; ?>,
        Name $name
    ): self {
        $event = self::occur($<?= $id_property; ?>->toString(), [
            'name' => $name->toString(),
        ]);

        $event->name = $name;

        return $event;
    }

    public function <?= $id_property; ?>(): <?= $id_class_short; ?><?= "\n"; ?>
    {
        return <?= $id_class_short; ?>::fromString($this->aggregateId());
    }

    public function name(): Name
    {
        if (!isset($this->name)) {
            $this->name = Name::fromString($this->payload['name']);
        }

        return $this->name;
    }
}
