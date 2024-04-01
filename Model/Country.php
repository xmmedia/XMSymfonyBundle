<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\DataProvider\CountryProvider;
use Xm\SymfonyBundle\Exception\InvalidCountry;

class Country implements ValueObject
{
    private string $abbreviation;

    private string $name;

    /**
     * @return static
     */
    public static function fromString(string $country): self
    {
        return new self($country);
    }

    private function __construct(string $abbreviation)
    {
        $abbreviation = strtoupper($abbreviation);

        try {
            Assert::length($abbreviation, 2);
            Assert::oneOf($abbreviation, CountryProvider::abbreviations());
        } catch (\InvalidArgumentException) {
            throw InvalidCountry::invalid($abbreviation);
        }

        $this->abbreviation = $abbreviation;
        $this->name = CountryProvider::name($abbreviation);
    }

    public function abbreviation(): string
    {
        return $this->abbreviation;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function toString(): string
    {
        return $this->abbreviation;
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

        return $this->abbreviation === $other->abbreviation;
    }
}
