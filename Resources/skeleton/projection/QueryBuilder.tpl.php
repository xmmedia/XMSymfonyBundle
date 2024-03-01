<?= "<?php\n"; ?>

declare(strict_types=1);

namespace <?= $namespace; ?>;

use Xm\SymfonyBundle\Doctrine\FilterQueryBuilder;
use Xm\SymfonyBundle\Util\FiltersInterface;

class <?= $class_name; ?> extends FilterQueryBuilder
{
    protected string $order = '<?= $projection_name_first_letter; ?>.<?= $name_field; ?> ASC';

    public function queryParts(<?= $filters_class_short; ?>|FiltersInterface $filters): array
    {
        if ($filters->applied(<?= $filters_class_short; ?>::Q)) {
            $this->applyBasicQ($filters, <?= $filters_class_short; ?>::Q, ['<?= $projection_name_first_letter; ?>.<?= $name_field; ?>']);
        }

        return [
            'join'           => implode(' ', $this->joins),
            'where'          => implode(' AND ', $this->whereClauses),
            'order'          => $this->order,
            'parameters'     => $this->parameters,
            'parameterTypes' => $this->parameterTypes,
        ];
    }
}
