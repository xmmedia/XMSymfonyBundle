<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;
use <?= $name_class; ?>;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

final class <?= $class_name; ?> extends AggregateChanged
{
    private Name $newName;
    private Name $oldName;

    public static function now(
        <?= $id_class_short; ?> $<?= $id_property; ?>,
        Name $newName,
        Name $oldName
    ): self {
        $event = self::occur($<?= $id_property; ?>->toString(), [
            'newName' => $newName->toString(),
            'oldName' => $oldName->toString(),
        ]);

        $event->newName = $newName;
        $event->oldName = $oldName;

        return $event;
    }

    public function <?= $id_property; ?>(): <?= $id_class_short; ?><?= "\n"; ?>
    {
        return <?= $id_class_short; ?>::fromString($this->aggregateId());
    }

    public function newName(): Name
    {
        if (!isset($this->newName)) {
            $this->newName = Name::fromString($this->payload['newName']);
        }

        return $this->newName;
    }

    public function oldName(): Name
    {
        if (!isset($this->oldName)) {
            $this->oldName = Name::fromString($this->payload['oldName']);
        }

        return $this->oldName;
    }
}
