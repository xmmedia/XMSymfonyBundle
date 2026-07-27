<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

/**
 * @phpstan-require-implements ValueObjectEnum
 */
trait ValueObjectEnumTrait
{
    /**
     * @return array<int, string|int>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * @return array<int, string>
     */
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    /**
     * @return array<string, string|int>
     */
    public static function namesToValues(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    public function sameValueAs(self|ValueObject $other): bool
    {
        return $this === $other;
    }
}
