<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $finder_class; ?>;
use <?= $filters_class; ?>;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class <?= $class_name; ?> implements QueryInterface
{
    public function __construct(private <?= $finder_class_short; ?> $<?= $finder_property; ?>)
    {
    }

    public function __invoke(?array $filters): int
    {
        return $this-><?= $finder_property; ?>->countByFilters(<?= $filters_class_short; ?>::fromArray($filters));
    }
}
