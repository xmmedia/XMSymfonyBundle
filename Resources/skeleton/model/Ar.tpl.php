<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRoot;
use Xm\SymfonyBundle\EventSourcing\AppliesAggregateChanged;
use Xm\SymfonyBundle\Model\Entity;

class <?= $class_name; ?> extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    /** @var <?= $id_class; ?> */
    private $<?= $id_property; ?>;

    /** @var bool */
    private $deleted = false;

    public static function create(
        <?= $id_class; ?> $<?= $id_property; ?>,
        Name $name
    ): self {
        $self = new self();
        $self->recordThat(
            Event\<?= $class_name; ?>WasAdded::now(
                $<?= $id_property; ?>,
                $name
            )
        );

        return $self;
    }

    public function update(Name $name): void
    {
        if ($this->deleted) {
            throw Exception\<?= $class_name; ?>IsDeleted::triedToUpdate($this-><?= $id_property; ?>);
        }

        $this->recordThat(
            Event\<?= $class_name; ?>WasUpdated::now(
                $this-><?= $id_property; ?>,
                $name
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

    protected function aggregateId(): string
    {
        return $this-><?= $id_property; ?>->toString();
    }

    protected function when<?= $class_name; ?>WasAdded(Event\<?= $class_name; ?>WasAdded $event): void
    {
        $this-><?= $id_property; ?> = $event-><?= $id_property; ?>();
    }

    protected function when<?= $class_name; ?>WasUpdated(Event\<?= $class_name; ?>WasUpdated $event): void
    {
        // noop
    }

    protected function when<?= $class_name; ?>WasDeleted(Event\<?= $class_name; ?>WasDeleted $event): void
    {
        $this->deleted = true;
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
