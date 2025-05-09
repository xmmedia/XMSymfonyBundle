<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Doctrine;

use Xm\SymfonyBundle\Util\FiltersInterface;

abstract class FilterQueryBuilder
{
    protected array $joins = [];
    protected array $whereClauses = [1];
    protected array $parameters = [];
    /**
     * @var array If using arrays, should one of the \Doctrine\DBAL\ArrayParameterType constants.
     */
    protected array $parameterTypes = [];
    protected string $order = '';

    abstract public function queryParts(FiltersInterface $filters): array;

    public function reset(): static
    {
        $ro = new \ReflectionClass(self::class);

        $this->joins = $ro->getProperty('joins')->getDefaultValue();
        $this->whereClauses = $ro->getProperty('whereClauses')->getDefaultValue();
        $this->parameters = $ro->getProperty('parameters')->getDefaultValue();
        $this->parameterTypes = $ro->getProperty('parameterTypes')->getDefaultValue();
        $this->order = $ro->getProperty('order')->getDefaultValue();

        return $this;
    }

    /**
     * Apply a basic filter to the query searching anywhere in the field.
     * Breaks apart the filter/query string by spaces and commas, and applies each part separately to the query using LIKE.
     */
    protected function applyBasicQ(FiltersInterface $filters, string $field, array $includeFields): void
    {
        $qParts = preg_split('/[ ,]/', trim($filters->get($field)));
        $qCriteria = [];

        foreach ($qParts as $i => $part) {
            foreach ($includeFields as $includeField) {
                $qCriteria[] = $includeField.' LIKE :q'.$i;
            }

            $this->parameters['q'.$i] = '%'.$part.'%';
        }

        $this->whereClauses[] = '('.implode(' OR ', $qCriteria).')';
    }
}
