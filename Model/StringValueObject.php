<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use Xm\SymfonyBundle\Util\StringUtil;

abstract class StringValueObject implements ValueObject
{
    /** @var string */
    private $string;

    public static function fromString(string $string): self
    {
        return new static($string);
    }

    private function __construct(string $string)
    {
        $string = StringUtil::trim($string);

        $this->string = $string;
    }

    public function toString(): string
    {
        return $this->string;
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
        if (static::class !== \get_class($other)) {
            return false;
        }

        return $this->string === $other->string;
    }
}
