<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $command_class; ?>;
use <?= $id_class; ?>;
<?php if (!$delete) { ?>
use <?= $entity_finder_class; ?>;
use <?= $entity_class; ?>;
use <?= $name_class; ?>;
<?php } ?>
use JetBrains\PhpStorm\ArrayShape;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class <?= $class_name; ?> implements MutationInterface
{
<?php if (!$delete) { ?>
    public function __construct(private MessageBusInterface $commandBus, private <?= $entity_finder; ?> $<?= $entity_finder_lower; ?>)
<?php } else { ?>
    public function __construct(private MessageBusInterface $commandBus)
<?php } ?>
    {
    }

<?php if (!$delete) { ?>
    #[ArrayShape(['<?= $entity; ?>' => <?= $entity_class_short; ?>::class])]
    public function __invoke(array $<?= $model_lower; ?>): array
    {
        $<?= $id_property; ?> = <?= $id_class_short; ?>::fromString($<?= $model_lower; ?>['<?= $id_property; ?>']);

        $this->commandBus->dispatch(
            <?= $command_class_short; ?>::now(
                $<?= $id_property; ?>,
                <?= $name_class_short ?>::fromString($<?= $model_lower; ?>['<?= $name_property ?>']),
            ),
        );

        return [
            '<?= $entity; ?>' => $this-><?= $entity_finder_lower; ?>-><?= $add ? 'find' : 'findRefreshed' ?>($<?= $id_property; ?>),
        ];
<?php } else { ?>
    #[ArrayShape(['success' => bool])]
    public function __invoke(string $<?= $id_property; ?>): array
    {
        $this->commandBus->dispatch(
            <?= $command_class_short; ?>::now(<?= $id_class_short; ?>::fromString($<?= $id_property; ?>)),
        );

        return [
            'success' => true,
        ];
<?php } ?>
    }
}
