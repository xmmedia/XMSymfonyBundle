<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $id_class; ?>;
use <?= $entity_finder_class; ?>;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class <?= $class_name; ?> implements QueryInterface
{
    public function __construct(private <?= $entity_filter_class_short; ?> $<?= $entity_finder_property; ?>)
    {
    }

    public function __invoke(<?= $id_class_short; ?> $<?= $id_property; ?>): ?<?= $entity_class_short; ?><?= "\n"; ?>
    {
        return $this-><?= $entity_finder_property; ?>->find($<?= $id_property; ?>);
    }
}
