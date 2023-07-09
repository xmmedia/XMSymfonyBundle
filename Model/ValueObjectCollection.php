<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

/**
 * Collection built for use with classes the implement the ValueObject interface,
 * including UUIDs.
 */
abstract class ValueObjectCollection extends Collection
{
    /** @var ValueObject[] */
    protected array $data = [];

    /**
     * @param ValueObject $element
     */
    public function contains(mixed $element, bool $strict = true): bool
    {
        foreach ($this->data as $item) {
            if ($item->sameValueAs($element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ValueObject $element
     */
    public function remove(mixed $element): bool
    {
        foreach ($this->data as $i => $item) {
            if ($item->sameValueAs($element)) {
                unset($this->data[$i]);

                return true;
            }
        }

        return false;
    }

    protected function getComparator(): \Closure
    {
        return function (ValueObject $a, ValueObject $b): int {
            return $a->sameValueAs($b) ? 0 : -1;
        };
    }
}
