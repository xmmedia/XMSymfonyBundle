<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $can_be_deleted_interface_class; ?>;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class <?= $class_name; ?> implements QueryInterface
{
    public function __construct(private <?= $can_be_deleted_interface_class_short; ?> $canBeDeleted)
    {
    }

    public function __invoke(<?= $entity_class_short; ?> $<?= $entity; ?>): bool
    {
        return ($this->canBeDeleted)($<?= $entity; ?>-><?= $id_property; ?>());
    }
}
