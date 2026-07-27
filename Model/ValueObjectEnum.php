<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

/**
 * For backed enums (string or int) used as value objects.
 *
 * Extending \BackedEnum means PHP itself rejects a non-enum implementing this.
 *
 * @see ValueObjectEnumTrait for the implementation
 */
interface ValueObjectEnum extends ValueObject, \BackedEnum
{
    /**
     * @return array<int, string|int>
     */
    public static function values(): array;

    /**
     * @return array<int, string>
     */
    public static function names(): array;

    /**
     * Name => value, e.g. for the values of a GraphQL enum type.
     *
     * @return array<string, string|int>
     */
    public static function namesToValues(): array;
}
