<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $command_class; ?>;
use <?= $id_class; ?>;
use <?= $entity_finder_class; ?>;
<?php if (!$delete) { ?>
use <?= $name_class; ?>;
<?php } ?>
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final class <?= $class_name; ?> implements MutationInterface
{
    private MessageBusInterface $commandBus;
<?php if (!$delete) { ?>
    private <?= $entity_finder; ?> $<?= $entity_finder_lower; ?>;
<?php } ?>

<?php if (!$delete) { ?>
    public function __construct(MessageBusInterface $commandBus, <?= $entity_finder; ?> $<?= $entity_finder_lower; ?>)
<?php } else { ?>
    public function __construct(MessageBusInterface $commandBus)
<?php } ?>
    {
        $this->commandBus = $commandBus;
<?php if (!$delete) { ?>
        $this-><?= $entity_finder_lower; ?> = $<?= $entity_finder_lower; ?>;
<?php } ?>
    }

<?php if (!$delete) { ?>
    public function __invoke(array $<?= $model_lower; ?>): array
    {
        $<?= $id_property; ?> = <?= $id_class_short; ?>::fromString($<?= $model_lower; ?>['<?= $id_property; ?>']);

        $this->commandBus->dispatch(
            <?= $command_class_short; ?>::now(
                $<?= $id_property; ?>,
                Name::fromString($<?= $model_lower; ?>['name']),
            ),
        );

        return [
            '<?= $entity; ?>' => $this-><?= $entity_finder_lower; ?>->findRefreshed($<?= $id_property; ?>),
        ];
<?php } else { ?>
    public function __invoke(string $<?= $id_property; ?>): array
    {
        $this->commandBus->dispatch(
            <?= $command_class_short; ?>::now(<?= $id_class_short; ?>::fromString($<?= $id_property; ?>))
        );

        return [
            'success' => true,
        ];
<?php } ?>
    }
}
