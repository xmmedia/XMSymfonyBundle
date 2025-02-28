<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Util;

use Webmozart\Assert\Assert;

abstract class Filters implements FiltersInterface
{
    protected array $filters;

    protected array $availableFields;

    /**
     * @return static
     */
    public static function fromArray(?array $filters): self
    {
        return new static($filters ?? []);
    }

    protected function __construct(array $filters)
    {
        $this->availableFields = $this->getFields();
        if (empty($this->availableFields)) {
            Assert::notEmpty(
                $this->availableFields,
                'The filter class must have at least 1 filter constant.',
            );
        }

        $filters = array_map([StringUtil::class, 'trim'], $filters);

        foreach ($filters as $key => $value) {
            Assert::oneOf(
                $key,
                $this->availableFields,
            );
        }

        $filters = $this->parseFilters($filters);

        $filters = array_filter($filters, [$this, 'notEmpty']);

        $this->filters = $filters;
    }

    public function applied(string $field): bool
    {
        return \array_key_exists($field, $this->filters);
    }

    public function get(string $field): mixed
    {
        return $this->filters[$field] ?? null;
    }

    public function toArray(): array
    {
        return $this->filters;
    }

    public function isTrue($value): bool
    {
        return true === $value || 'true' === $value;
    }

    protected function parseFilters(array $filters): array
    {
        return $filters;
    }

    protected function notEmpty($value): bool
    {
        return !(empty($value) && !\is_bool($value) && !\is_int($value) && '0' !== $value);
    }

    private function getFields(): array
    {
        $reflection = new \ReflectionClass(static::class);
        $constants = [];

        foreach ($reflection->getReflectionConstants() as $reflConstant) {
            if ($reflConstant->isPublic()) {
                $constants[$reflConstant->getName()] = $reflConstant->getValue();
            }
        }

        return $constants;
    }
}
