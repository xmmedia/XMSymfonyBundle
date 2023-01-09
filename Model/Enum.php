<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use MabeEnum\Enum as MabeEnum;
use MabeEnum\EnumSerializableTrait;

abstract class Enum extends MabeEnum implements \Serializable, ValueObject
{
    use EnumSerializableTrait;

    public function sameValueAs(ValueObject $object): bool
    {
        return $this->is($object);
    }

    public function oneOf(iterable $enumerators): bool
    {
        foreach ($enumerators as $enumerator) {
            if ($this->is($enumerator)) {
                return true;
            }
        }

        return false;
    }
}
