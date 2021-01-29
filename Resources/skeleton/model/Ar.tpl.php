<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRoot;
use Xm\SymfonyBundle\EventSourcing\AppliesAggregateChanged;
use Xm\SymfonyBundle\Model\Entity;

class <?= $class_name; ?> extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    private <?= $id_class; ?> $<?= $id_property; ?>;
    private Name $name;
    private bool $deleted = false;

    public static function add(
        <?= $id_class; ?> $<?= $id_property; ?>,
        Name $name
    ): self {
        $self = new self();
        $self->recordThat(
            Event\<?= $class_name; ?>WasAdded::now(
                $<?= $id_property; ?>,
                $name,
            )
        );

        return $self;
    }

    public function changeName(Name $newName): void
    {
        if ($this->name->sameValueAs($newName)) {
            return;
        }

        if ($this->deleted) {
            throw Exception\<?= $class_name; ?>IsDeleted::triedToChangeName($this-><?= $id_property; ?>);
        }

        $this->recordThat(
            Event\<?= $class_name; ?>NameWasChanged::now(
                $this-><?= $id_property; ?>,
                $newName,
                $this->name,
            )
        );
    }

    public function delete(): void
    {
        if ($this->deleted) {
            throw Exception\<?= $class_name; ?>IsDeleted::triedToDelete($this-><?= $id_property; ?>);
        }

        $this->recordThat(
            Event\<?= $class_name; ?>WasDeleted::now($this-><?= $id_property; ?>)
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
        $this->name = $event->name();
    }

    protected function when<?= $class_name; ?>NameWasChanged(Event\<?= $class_name; ?>NameWasChanged $event): void
    {
        $this->name = $event->newName();
    }

    protected function when<?= $class_name; ?>WasDeleted(Event\<?= $class_name; ?>WasDeleted $event): void
    {
        $this->deleted = true;
    }

    public function <?= $id_property; ?>(): <?= $id_class; ?><?= "\n"; ?>
    {
        return $this-><?= $id_property; ?>;
    }

    public function name(): Name
    {
        return $this->name;
    }

    /**
     * @param <?= $class_name; ?>|Entity $other
     */
    public function sameIdentityAs(Entity $other): bool
    {
        if (static::class !== \get_class($other)) {
            return false;
        }

        return $this-><?= $id_property; ?>->sameValueAs($other-><?= $id_property; ?>);
    }
}
