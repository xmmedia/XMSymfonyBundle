<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use App\Model\<?= $model; ?>\Event;
use Prooph\Bundle\EventStore\Projection\ReadModelProjection;
use Prooph\EventStore\Projection\ReadModelProjector;

/**
 * @method \Prooph\EventStore\Projection\ReadModel readModel()
 */
class <?= $class_name; ?> implements ReadModelProjection
{
    public function project(ReadModelProjector $projector): ReadModelProjector
    {
        $projector->fromStream('<?= $stream_name; ?>')
            ->when([
                Event\<?= $model; ?>WasCreated::class => function (
                    array $state,
                    Event\<?= $model; ?>WasCreated $event
                ): void {
                    /** @var <?= $model; ?>ReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('insert', [
                        '<?= $id_field; ?>' => $event->aggregateId(),
                        'name' => $event->name()->toString(),
                    ]);
                },

                Event\<?= $model; ?>WasUpdated ::class => function (
                    array $state,
                    Event\<?= $model; ?>WasUpdated $event
                ): void {
                    /** @var <?= $model; ?>ReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack(
                        'update',
                        $event->aggregateId(),
                        [
                            'name' => $event->name()->toString(),
                        ]
                    );
                },

                Event\<?= $model; ?>WasDeleted ::class => function (
                    array $state,
                    Event\<?= $model; ?>WasDeleted $event
                ): void {
                    /** @var <?= $model; ?>ReadModel $readModel */
                    /** @var ReadModelProjector $this */
                    $readModel = $this->readModel();
                    $readModel->stack('remove', $event->aggregateId());
                },
            ]);

        return $projector;
    }
}
