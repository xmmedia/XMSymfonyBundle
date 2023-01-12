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
     * {@inheritDoc}
     *
     * @param ValueObject $element
     */
    public function contains($element, bool $strict = true): bool
    {
        foreach ($this->data as $item) {
            if ($item->sameValueAs($element)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @param ValueObject $element
     */
    public function remove($element): bool
    {
        foreach ($this->data as $i => $item) {
            if ($item->sameValueAs($element)) {
                unset($this->data[$i]);

                return true;
            }
        }

        return false;
    }
}
