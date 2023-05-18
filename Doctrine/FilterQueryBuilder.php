<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Doctrine;

use Xm\SymfonyBundle\Util\Filters;

abstract class FilterQueryBuilder
{
    protected array $joins = [];
    protected array $whereClauses = [1];
    protected array $parameters = [];
    protected array $parameterTypes = [];
    protected string $order = '';

    abstract public function queryParts(Filters $filters): array;

    protected function applyBasicQ(Filters $filters, string $field, array $includeFields): void
    {
        $qParts = preg_split('/[ ,]/', trim($filters->get($field)));
        $qCriteria = [];

        foreach ($qParts as $i => $part) {
            foreach ($includeFields as $field) {
                $qCriteria[] = 'u.'.$field.' LIKE :q'.$i;
            }

            $this->parameters['q'.$i] = '%'.$part.'%';
        }

        $this->whereClauses[] = '('.implode(' OR ', $qCriteria).')';
    }
}
