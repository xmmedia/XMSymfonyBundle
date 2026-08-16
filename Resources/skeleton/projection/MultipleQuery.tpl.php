<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $entity_finder_class; ?>;
use <?= $filters_class; ?>;
use JetBrains\PhpStorm\ArrayShape;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class <?= $class_name; ?> implements QueryInterface
{
    public function __construct(private <?= $entity_filter_class_short; ?> $<?= $entity_finder_property; ?>)
    {
    }

    /**
     * @param array<string, mixed>|null $filters
     *
     * @return <?= $entity_class_short; ?>[]
     */
    #[ArrayShape([<?= $entity_class_short; ?>::class])]
    public function __invoke(?array $filters): array
    {
        return $this-><?= $entity_finder_property; ?>->findByFilters(<?= $filters_class_short; ?>::fromArray($filters));
    }
}
