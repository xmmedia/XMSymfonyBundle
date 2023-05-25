<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

abstract class Collection extends \Ramsey\Collection\Collection implements \JsonSerializable
{
    public static function fromArray(array $data = []): static
    {
        return new static('mixed', $data);
    }

    public function find(callable $cb): mixed
    {
        $collection = $this->filter($cb);

        if ($collection->isEmpty()) {
            return null;
        }

        return $collection->first();
    }

    public function sameValuesAs(self $other): bool
    {
        if (static::class !== $other::class) {
            return false;
        }

        if ($this->count() !== $other->count()) {
            return false;
        }

        return 0 === $this->diff($other)->count();
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
