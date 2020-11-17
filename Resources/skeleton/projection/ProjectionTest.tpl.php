<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use App\Model\<?= $model; ?>\Event;
use <?= $projection_class; ?>;
use App\Tests\BaseTestCase;
use Mockery;
use Prooph\EventStore\Projection\ReadModelProjector;
use Xm\SymfonyBundle\Tests\ProjectionWithArgs;

class <?= $class_name; ?> extends BaseTestCase
{
    use ProjectionWithArgs;

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
            ->withArgs($this->whenArgs($projectedEvents));

        $projection->project($projector);
    }
}
