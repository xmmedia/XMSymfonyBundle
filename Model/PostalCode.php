<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Exception\InvalidPostalCode;
use Xm\SymfonyBundle\Util\StringUtil;

/**
 * Canada: 6 alphanumeric (without space(s))
 * US: 5 or 10 numbers (5+4 digits plus dash).
 */
class PostalCode implements ValueObject
{
    public const MIN_LENGTH = 5;
    public const MAX_LENGTH = 10;

    private string $postalCode;

    /**
     * @return static
     */
    public static function fromString(string $postalCode): self
    {
        return new static($postalCode);
    }

    private function __construct(string $postalCode)
    {
        $postalCode = static::clean($postalCode);

        static::validate($postalCode);

        $this->postalCode = self::format($postalCode);
    }

    protected static function clean(string $postalCode): string
    {
        return strtoupper(str_replace([' ', '-'], '', StringUtil::trim($postalCode)));
    }

    protected static function validate(string $postalCode): void
    {
        try {
            Assert::lengthBetween(
                $postalCode,
                self::MIN_LENGTH,
                self::MAX_LENGTH,
            );
        } catch (\InvalidArgumentException $e) {
            throw InvalidPostalCode::invalid($postalCode);
        }
    }

    public static function format(string $postalCode): string
    {
        // if first char is a letter, we're assuming it's a Canadian postal code
        if (ctype_alpha(substr($postalCode, 0, 1))) {
            $postalCode = strtoupper(str_replace([' ', '-'], '', $postalCode));

            $postalCode = sprintf(
                '%s %s',
                substr($postalCode, 0, 3),
                substr($postalCode, 3, 3),
            );
        }

        return $postalCode;
    }

    public function toString(): string
    {
        return (string) $this->postalCode;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (static::class !== $other::class) {
            return false;
        }

        return $this->postalCode === $other->postalCode;
    }
}
