<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber as LibPhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

class PhoneNumber implements ValueObject
{
    private static string $defaultRegion = 'CA';

    private LibPhoneNumber $phoneNumber;

    /**
     * @return static
     */
    public static function fromArray(array $data): self
    {
        $util = PhoneNumberUtil::getInstance();

        try {
            $phoneNumber = $util->parse($data['phoneNumber'], self::$defaultRegion);
            if (!empty($data['extension'])) {
                $phoneNumber->setExtension($data['extension']);
            }
        } catch (NumberParseException $e) {
            throw new \InvalidArgumentException(sprintf('The phone number is invalid: %s', $e->getMessage()));
        }

        return new static($phoneNumber);
    }

    /**
     * @return static
     */
    public static function fromString(string $phoneNumber): self
    {
        $util = PhoneNumberUtil::getInstance();

        try {
            return new static(
                $util->parse($phoneNumber, self::$defaultRegion),
            );
        } catch (NumberParseException $e) {
            throw new \InvalidArgumentException(sprintf('The phone number "%s" is invalid: %s', $phoneNumber, $e->getMessage()));
        }
    }

    /**
     * @return static
     */
    public static function fromObject(LibPhoneNumber $phoneNumber): self
    {
        return new static($phoneNumber);
    }

    private function __construct(LibPhoneNumber $phoneNumber)
    {
        $util = PhoneNumberUtil::getInstance();

        if (!$util->isValidNumber($phoneNumber)) {
            throw new \InvalidArgumentException(sprintf('The phone number "%s" is invalid.', $phoneNumber));
        }

        $this->phoneNumber = $phoneNumber;
    }

    public function phoneNumber(): string
    {
        return $this->e164();
    }

    public function national(): string
    {
        $util = PhoneNumberUtil::getInstance();

        return $util->format($this->phoneNumber, PhoneNumberFormat::NATIONAL);
    }

    public function extension(): ?string
    {
        return $this->phoneNumber->getExtension();
    }

    public function obj(): LibPhoneNumber
    {
        return $this->phoneNumber;
    }

    public function toArray(): array
    {
        return [
            'phoneNumber' => $this->e164(),
            'extension'   => $this->phoneNumber->getExtension(),
        ];
    }

    public function __toString(): string
    {
        return $this->e164();
    }

    /**
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (static::class !== \get_class($other)) {
            return false;
        }

        return $this->phoneNumber->equals($other->phoneNumber);
    }

    private function e164(): string
    {
        $util = PhoneNumberUtil::getInstance();

        return $util->format($this->phoneNumber, PhoneNumberFormat::E164);
    }
}
