<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

/**
 * Iterator to allow multiple iterators to be concatenated.
 */
class ConcatIterator extends \AppendIterator implements \ArrayAccess, \Countable, \JsonSerializable
{
    public const INVALID_INDEX = 'Index invalid or out of range';

    /** Fast-lookup count for full set of iterators */
    public int $count = 0;

    /**
     * Build an iterator over multiple iterators
     * Unlike a LimitIterator, the $end defines the last index, not the count.
     *
     * @param \Iterator $iterator,... Concat iterators in order
     */
    public function __construct()
    {
        parent::__construct();
        foreach (\func_get_args() as $i => $iterator) {
            if (
                $iterator instanceof \ArrayAccess
                && $iterator instanceof \Countable
            ) {
                // Unroll other ConcatIterators, so we avoid deep iterator stacks
                if ($iterator instanceof self) {
                    foreach ($iterator->getArrayIterator() as $innerIt) {
                        $this->append($innerIt);
                    }
                } else {
                    $this->append($iterator);
                }
                $this->count += \count($iterator);
            } else {
                throw new \InvalidArgumentException('Argument '.$i.' passed to '.__METHOD__.' must be of type ArrayAccess, Countable, and Traversable. '.\gettype($iterator).' given.');
            }
        }
    }

    /**
     * Countable.
     */
    public function count(): int
    {
        return $this->count;
    }

    /**
     * ArrayAccess.
     */
    public function offsetExists($offset): bool
    {
        return $offset >= 0 && $offset < $this->count;
    }

    public function offsetGet($offset): mixed
    {
        if ($this->offsetExists($offset)) {
            [$it, $idx] = $this->getIteratorByIndex($offset);

            return $it->offsetGet($idx);
        } else {
            throw new \RuntimeException(self::INVALID_INDEX);
        }
    }

    public function offsetSet($offset, $value): void
    {
        [$it, $idx] = $this->getIteratorByIndex($offset);
        $it->offsetSet($idx, $value);
    }

    public function offsetUnset($offset): void
    {
        [$it, $idx] = $this->getIteratorByIndex($offset);
        $it->offsetUnset($idx);
    }

    /**
     * JsonSerializable.
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return iterator_to_array($this, false);
    }

    /**
     * Find which of the inner iterators an index corresponds to.
     *
     * @param int $index
     *
     * @return [ArrayAccess, int] The iterator and interior index
     */
    protected function getIteratorByIndex($index = 0)
    {
        $runningCount = 0;
        foreach ($this->getArrayIterator() as $innerIt) {
            $count = \count($innerIt);
            if ($index < $runningCount + $count) {
                return [$innerIt, $index - $runningCount];
            }
            $runningCount += $count;
        }

        return null;
    }
}
