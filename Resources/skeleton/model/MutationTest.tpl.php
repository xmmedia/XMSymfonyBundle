<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $command_class; ?>;
use <?= $mutation_class; ?>;
<?php if (!$delete) { ?>
use <?= $id_class; ?>;
use <?= $entity_class; ?>;
use <?= $entity_finder_class; ?>;
<?php } ?>
use App\Tests\BaseTestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class <?= $class_name; ?> extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();
<?php if (!$delete) { ?>
        $args = [
            '<?= $id_property; ?>' => $faker->uuid(),
            'name' => $faker->name(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(<?= $command_class_short; ?>::class))
            ->andReturn(new Envelope(new \stdClass()));

        $entity = \Mockery::mock(<?= $entity_class_short; ?>::class);

        $<?= $entity_finder_lower; ?> = \Mockery::mock(<?= $entity_finder; ?>::class);
        $<?= $entity_finder_lower; ?>->shouldReceive('<?= $add ? 'find' : 'findRefreshed' ?>')
            ->once()
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->andReturn($entity);

        $result = (new <?= $mutation_class_short; ?>($commandBus, $<?= $entity_finder_lower; ?>))($args);

        $expected = [
            '<?= $entity; ?>' => $entity,
        ];

        $this->assertEquals($expected, $result);
<?php } else { ?>
        $<?= $id_property; ?> = $faker->uuid();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(<?= $command_class_short; ?>::class))
            ->andReturn(new Envelope(new \stdClass()));

        $result = (new <?= $mutation_class_short; ?>($commandBus))($<?= $id_property; ?>);

        $expected = [
            'success' => true,
        ];

        $this->assertEquals($expected, $result);
<?php } ?>
    }
}
