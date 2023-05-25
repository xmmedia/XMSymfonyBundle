<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Exception\InvalidAddress;
use Xm\SymfonyBundle\Util\StringUtil;

class Address implements ValueObject
{
    public const LINE_MIN_LENGTH = 3;
    public const LINE_MAX_LENGTH = 100;
    public const CITY_MIN_LENGTH = 2;
    public const CITY_MAX_LENGTH = 50;

    /**
     * @return static
     */
    public static function fromStrings(
        string $line1,
        ?string $line2,
        string $city,
        string $province,
        string $postalCode,
        string $country,
    ): self {
        $province = Province::fromString($province);
        $postalCode = PostalCode::fromString($postalCode);
        $country = Country::fromString($country);

        return new self($line1, $line2, $city, $province, $postalCode, $country);
    }

    /**
     * @return static
     */
    public static function fromArray(array $address): self
    {
        if (!$address['province'] instanceof Province) {
            $address['province'] = Province::fromString($address['province']);
        }
        if (!$address['postalCode'] instanceof PostalCode) {
            $address['postalCode'] = PostalCode::fromString($address['postalCode']);
        }
        if (!$address['country'] instanceof Country) {
            $address['country'] = Country::fromString($address['country']);
        }

        return new self(
            $address['line1'],
            $address['line2'] ?? null,
            $address['city'],
            $address['province'],
            $address['postalCode'],
            $address['country'],
        );
    }

    private function __construct(
        private string $line1,
        private ?string $line2,
        private string $city,
        private Province $province,
        private PostalCode $postalCode,
        private Country $country,
    ) {
        $line1 = StringUtil::trim($line1);
        $line2 = StringUtil::trim($line2);
        $city = StringUtil::trim($city);

        try {
            Assert::lengthBetween(
                $line1,
                self::LINE_MIN_LENGTH,
                self::LINE_MAX_LENGTH,
            );
        } catch (\InvalidArgumentException $e) {
            throw InvalidAddress::line1($line1, $e);
        }

        try {
            Assert::nullOrLengthBetween(
                $line2,
                self::LINE_MIN_LENGTH,
                self::LINE_MAX_LENGTH,
            );
        } catch (\InvalidArgumentException $e) {
            throw InvalidAddress::line2($line2, $e);
        }

        try {
            Assert::lengthBetween(
                $city,
                self::CITY_MIN_LENGTH,
                self::CITY_MAX_LENGTH,
            );
        } catch (\InvalidArgumentException $e) {
            throw InvalidAddress::city($city, $e);
        }

        $this->line1 = $line1;
        $this->line2 = $line2;
        $this->city = $city;
        $this->province = $province;
        $this->postalCode = $postalCode;
        $this->country = $country;
    }

    public function line1(): string
    {
        return $this->line1;
    }

    public function line2(): ?string
    {
        return $this->line2;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function province(): Province
    {
        return $this->province;
    }

    public function postalCode(): PostalCode
    {
        return $this->postalCode;
    }

    public function country(): Country
    {
        return $this->country;
    }

    public function toString(bool $html = false, bool $includeCountry = true): string
    {
        $br = $html ? '<br>' : "\n";

        $str = $this->line1;
        if (null !== $this->line2) {
            $str .= $br.$this->line2;
        }
        $str .= $br.$this->city.', '.$this->province->abbreviation();
        $str .= ($html ? ' &nbsp;' : '  ').$this->postalCode;
        if ($includeCountry) {
            $str .= $br . $this->country->name();
        }

        return $str;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toArray(): array
    {
        return [
            'line1'      => $this->line1,
            'line2'      => $this->line2,
            'city'       => $this->city,
            'province'   => $this->province->toString(),
            'postalCode' => $this->postalCode->toString(),
            'country'    => $this->country->toString(),
        ];
    }

    /**
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (static::class !== $other::class) {
            return false;
        }

        return $this->toArray() === $other->toArray();
    }
}
