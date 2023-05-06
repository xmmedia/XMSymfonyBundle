<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $id_class; ?>;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

class <?= $class_name; ?> extends AggregateChanged
{
    public static function now(<?= $id_class_short; ?> $<?= $id_property; ?>): self
    {
        $event = self::occur($<?= $id_property; ?>->toString());

        return $event;
    }

    public function <?= $id_property; ?>(): <?= $id_class_short; ?><?= "\n"; ?>
    {
        return <?= $id_class_short; ?>::fromString($this->aggregateId());
    }
}
