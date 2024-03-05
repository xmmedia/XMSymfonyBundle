<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_finder_class; ?>;
use <?= $filters_class; ?>;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class <?= $class_name; ?> implements QueryInterface
{
    public function __construct(private <?= $entity_filter_class_short; ?> $<?= $entity_finder_property; ?>)
    {
    }

    public function __invoke(?array $filters): int
    {
        return $this-><?= $entity_finder_property; ?>->countByFilters(<?= $filters_class_short; ?>::fromArray($filters));
    }
}
