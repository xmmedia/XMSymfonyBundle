<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use MabeEnum\Enum as MabeEnum;
use MabeEnum\EnumSerializableTrait;

/**
 * Use a native backed enum implementing ValueObjectEnum (with ValueObjectEnumTrait) for new enums.
 * This remains for existing enums built on marc-mabe/php-enum.
 */
abstract class Enum extends MabeEnum implements \Serializable, ValueObject
{
    use EnumSerializableTrait;

    public function sameValueAs(self|ValueObject $object): bool
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
