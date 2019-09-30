<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\DataProvider\ProvinceProvider;
use Xm\SymfonyBundle\Exception\InvalidProvince;

class Province implements ValueObject
{
    /** @var string */
    private $abbreviation;

    /** @var string */
    private $name;

    /** @var Country */
    private $country;

    public static function fromString(string $abbreviation): self
    {
        return new self($abbreviation);
    }

    private function __construct(string $abbreviation)
    {
        $abbreviation = strtoupper($abbreviation);

        try {
            Assert::length($abbreviation, 2);
            Assert::oneOf(
                $abbreviation,
                ProvinceProvider::abbreviations(false)
            );
        } catch (\InvalidArgumentException $e) {
            throw InvalidProvince::invalid($abbreviation);
        }

        $this->abbreviation = $abbreviation;
        $this->name = ProvinceProvider::name($abbreviation);
        $this->country = ProvinceProvider::country($abbreviation);
    }

    public function abbreviation(): string
    {
        return $this->abbreviation;
    }

    public function country(): Country
    {
        return $this->country;
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
        if (\get_class($this) !== \get_class($other)) {
            return false;
        }

        return $this->abbreviation === $other->abbreviation;
    }
}
