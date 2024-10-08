<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $command_class; ?>;
<?php if (!$delete) { ?>
use <?= $entity_finder_class; ?>;
use <?= $entity_class; ?>;
use <?= $name_class; ?>;
<?php } else { ?>
use <?= $id_class; ?>;
<?php } ?>
use JetBrains\PhpStorm\ArrayShape;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class <?= $class_name; ?> implements MutationInterface
{
<?php if (!$delete) { ?>
    public function __construct(private MessageBusInterface $commandBus, private <?= $entity_filter_class_short; ?> $<?= $entity_finder_property; ?>)
<?php } else { ?>
    public function __construct(private MessageBusInterface $commandBus)
<?php } ?>
    {
    }

<?php if (!$delete) { ?>
    #[ArrayShape(['<?= $entity; ?>' => <?= $entity_class_short; ?>::class])]
    public function __invoke(array $<?= $model_lower; ?>): array
    {
        $this->commandBus->dispatch(
            <?= $command_class_short; ?>::now(
                $<?= $model_lower; ?>['<?= $id_property; ?>'],
                <?= $name_class_short ?>::fromString($<?= $model_lower; ?>['<?= $name_property ?>']),
            ),
        );

        return [
            '<?= $entity; ?>' => $this-><?= $entity_finder_property; ?>-><?= $add ? 'find' : 'findRefreshed' ?>($<?= $model_lower; ?>['<?= $id_property; ?>']),
        ];
<?php } else { ?>
    #[ArrayShape(['success' => 'boolean'])]
    public function __invoke(<?= $id_class_short; ?> $<?= $id_property; ?>): array
    {
        $this->commandBus->dispatch(
            <?= $command_class_short; ?>::now($<?= $id_property; ?>),
        );

        return [
            'success' => true,
        ];
<?php } ?>
    }
}
