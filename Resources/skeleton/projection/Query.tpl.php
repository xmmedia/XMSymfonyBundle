<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $id_class; ?>;
use <?= $finder_class; ?>;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class <?= $class_name; ?> implements QueryInterface
{
    public function __construct(private <?= $finder_class_short; ?> $<?= $finder_property; ?>)
    {
    }

    public function __invoke(string $<?= $id_property; ?>): ?<?= $entity_class_short; ?><?= "\n"; ?>
    {
        return $this-><?= $finder_property; ?>->find(<?= $id_class_short; ?>::fromString($<?= $id_property; ?>));
    }
}
