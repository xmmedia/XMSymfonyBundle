<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $id_class; ?>;
use <?= $finder_class; ?>;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final class <?= $class_name; ?> implements QueryInterface
{
    private <?= $finder_class_short; ?> $<?= $finder_property; ?>;

    public function __construct(<?= $finder_class_short; ?> $<?= $finder_property; ?>)
    {
        $this-><?= $finder_property; ?> = $<?= $finder_property; ?>;
    }

    public function __invoke(string $<?= $id_property; ?>): ?<?= $entity_class_short; ?><?= "\n"; ?>
    {
        return $this-><?= $finder_property; ?>->find(<?= $id_class_short; ?>::fromString($<?= $id_property; ?>));
    }
}
