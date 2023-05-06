<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $finder_class; ?>;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;

final readonly class <?= $class_name; ?> implements QueryInterface
{
    public function __construct(private <?= $finder_class_short; ?> $<?= $finder_property; ?>)
    {
    }

    /**
     * @return <?= $entity_class_short; ?>[]
     */
    public function __invoke(): array
    {
        return $this-><?= $finder_property; ?>->findAll();
    }
}
