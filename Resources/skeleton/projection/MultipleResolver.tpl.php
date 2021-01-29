<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use <?= $entity_class; ?>;
use <?= $finder_class; ?>;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

final class <?= $class_name; ?> implements ResolverInterface
{
    private <?= $finder_class_short; ?> $<?= $finder_property; ?>;

    public function __construct(<?= $finder_class_short; ?> $<?= $finder_property; ?>)
    {
        $this-><?= $finder_property; ?> = $<?= $finder_property; ?>;
    }

    /**
     * @return <?= $entity_class_short; ?>[]
     */
    public function __invoke(): array
    {
        return $this-><?= $finder_property; ?>->findAll();
    }
}
