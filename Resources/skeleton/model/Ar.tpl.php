<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $name_class; ?>;
use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRoot;
use Xm\SymfonyBundle\EventSourcing\AppliesAggregateChanged;
use Xm\SymfonyBundle\Model\Entity;

class <?= $class_name; ?> extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    private <?= $id_class_short; ?> $<?= $id_property; ?>;
    private <?= $name_class_short; ?> $<?= $name_property; ?>;
    private bool $deleted = false;

    public static function add(
        <?= $id_class_short; ?> $<?= $id_property; ?>,
        <?= $name_class_short; ?> $<?= $name_property; ?>,
    ): self {
        $self = new self();
        $self->recordThat(
            Event\<?= $class_name; ?>WasAdded::now(
                $<?= $id_property; ?>,
                $<?= $name_property; ?>,
            ),
        );

        return $self;
    }

    public function change(<?= $name_class_short; ?> $new<?= $name_class_short; ?>): void
    {
        if ($this-><?= $name_property; ?>->sameValueAs($new<?= $name_class_short; ?>)) {
            return;
        }

        if ($this->deleted) {
            throw Exception\<?= $class_name; ?>IsDeleted::triedToChange($this-><?= $id_property; ?>);
        }

        $this->recordThat(
            Event\<?= $class_name; ?>WasChanged::now(
                $this-><?= $id_property; ?>,
                $new<?= $name_class_short; ?>,
                $this-><?= $name_property; ?>,
            ),
        );
    }

    public function delete(): void
    {
        if ($this->deleted) {
            throw Exception\<?= $class_name; ?>IsDeleted::triedToDelete($this-><?= $id_property; ?>);
        }

        $this->recordThat(
            Event\<?= $class_name; ?>WasDeleted::now($this-><?= $id_property; ?>),
        );
    }

    /**
     * @codeCoverageIgnore
     */
    protected function aggregateId(): string
    {
        return $this-><?= $id_property; ?>->toString();
    }

    protected function when<?= $class_name; ?>WasAdded(Event\<?= $class_name; ?>WasAdded $event): void
    {
        $this-><?= $id_property; ?> = $event-><?= $id_property; ?>();
        $this-><?= $name_property; ?> = $event-><?= $name_property; ?>();
    }

    protected function when<?= $class_name; ?>WasChanged(Event\<?= $class_name; ?>WasChanged $event): void
    {
        $this-><?= $name_property; ?> = $event->new<?= $name_class_short; ?>();
    }

    protected function when<?= $class_name; ?>WasDeleted(Event\<?= $class_name; ?>WasDeleted $event): void
    {
        $this->deleted = true;
    }

    public function <?= $id_property; ?>(): <?= $id_class_short; ?><?= "\n"; ?>
    {
        return $this-><?= $id_property; ?>;
    }

    public function <?= $name_property; ?>(): <?= $name_class_short; ?><?= "\n"; ?>
    {
        return $this-><?= $name_property; ?>;
    }

    /**
     * @param <?= $class_name; ?>|Entity $other
     */
    public function sameIdentityAs(Entity $other): bool
    {
        if (static::class !== $other::class) {
            return false;
        }

        return $this-><?= $id_property; ?>->sameValueAs($other-><?= $id_property; ?>);
    }
}
