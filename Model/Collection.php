<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

abstract class Collection extends \Ramsey\Collection\Collection implements \JsonSerializable
{
    public static function fromArray(array $data = [])
    {
        return new static('mixed', $data);
    }

    public function find(callable $cb)
    {
        $collection = $this->filter($cb);

        if ($collection->isEmpty()) {
            return;
        }

        return $collection->first();
    }

    public function sameValuesAs(self $other): bool
    {
        if (static::class !== \get_class($other)) {
            return false;
        }

        if ($this->count() !== $other->count()) {
            return false;
        }

        return 0 === $this->diff($other)->count();
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
