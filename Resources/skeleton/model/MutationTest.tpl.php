<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $command_class; ?>;
use <?= $mustation_class; ?>;
use App\Tests\BaseTestCase;
use Mockery;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class <?= $class_name; ?> extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();
<?php if (!$delete) { ?>
        $args = [
            '<?= $id_property; ?>' => $faker->uuid,
            'name' => $faker->name,
        ];

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(<?= $command_class_short; ?>::class))
            ->andReturn(new Envelope(new \stdClass()));

        $result = (new <?= $mustation_class_short; ?>($commandBus))($args);

        $expected = [
            '<?= $id_property; ?>' => $args['<?= $id_property; ?>'],
        ];

        $this->assertEquals($expected, $result);
<?php } else { ?>
        $<?= $id_property; ?> = $faker->uuid;

        $commandBus = Mockery::mock(MessageBusInterface::class);
        $commandBus->shouldReceive('dispatch')
            ->once()
            ->with(Mockery::type(<?= $command_class_short; ?>::class))
            ->andReturn(new Envelope(new \stdClass()));

        $result = (new <?= $mustation_class_short; ?>($commandBus))($<?= $id_property; ?>);

        $expected = [
            'success' => true,
        ];

        $this->assertEquals($expected, $result);
<?php } ?>
    }
}
