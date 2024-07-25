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
            '<?= $id_property; ?>' => $faker-><?= $id_property; ?>(),
            '<?= $name_property ?>' => $faker->name(),
        ];

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(<?= $command_class_short; ?>::class))
            ->andReturn(new Envelope(new \stdClass()));

        $entity = \Mockery::mock(<?= $entity_class_short; ?>::class);

        $<?= $entity_finder_property; ?> = \Mockery::mock(<?= $entity_filter_class_short; ?>::class);
        $<?= $entity_finder_property; ?>->shouldReceive('<?= $add ? 'find' : 'findRefreshed' ?>')
            ->once()
            ->with(\Mockery::type(<?= $id_class_short; ?>::class))
            ->andReturn($entity);

        $result = (new <?= $mutation_class_short; ?>($commandBus, $<?= $entity_finder_property; ?>))($args);

        $expected = [
            '<?= $entity; ?>' => $entity,
        ];

        $this->assertSame($expected, $result);
<?php } else { ?>
        $<?= $id_property; ?> = $faker-><?= $id_property; ?>();

        $commandBus = \Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(\Mockery::type(<?= $command_class_short; ?>::class))
            ->andReturn(new Envelope(new \stdClass()));

        $result = (new <?= $mutation_class_short; ?>($commandBus))($<?= $id_property; ?>);

        $expected = [
            'success' => true,
        ];

        $this->assertSame($expected, $result);
<?php } ?>
    }
}
