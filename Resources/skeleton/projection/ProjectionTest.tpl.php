<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use App\Model\<?= $model; ?>\Event;
use <?= $projection_class; ?>;
use App\Tests\BaseTestCase;
use Mockery;
use Prooph\EventStore\Projection\ReadModelProjector;

class <?= $class_name; ?> extends BaseTestCase
{
    public function test(): void
    {
        $projectedEvents = [
            Event\<?= $model; ?>WasCreated::class,
            Event\<?= $model; ?>NameWasChanged::class,
            Event\<?= $model; ?>WasDeleted::class,
        ];

        $projection = new <?= $projection_class_short; ?>();

        $projector = Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with('<?= $stream_name; ?>')
            ->andReturnSelf();

        $projector->shouldReceive('when')
            ->withArgs(function ($eventHandlers) use ($projectedEvents) {
                if (!\is_array($eventHandlers)) {
                    return false;
                }

                // make sure all events in list are used
                foreach ($projectedEvents as $event) {
                    if (!\array_key_exists($event, $eventHandlers)) {
                        return false;
                    }
                }

                // make sure there are not extra events
                foreach ($eventHandlers as $event => $handler) {
                    if (false === array_search($event, $projectedEvents)) {
                        return false;
                    }
                }

                return true;
            });

        $projection->project($projector);
    }
}
