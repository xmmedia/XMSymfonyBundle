<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use Carbon\CarbonImmutable;
use Carbon\CarbonTimeZone;

class Date implements ValueObject
{
    public const STRING_FORMAT = 'Y-m-d';
    public const TZ = 'UTC';

    private CarbonImmutable $date;

    /**
     * @param string|CarbonTimeZone $tz
     *
     * @return static
     */
    public static function fromString(string $string, $tz = self::TZ): self
    {
        // timezone is only used if it's not in the date string
        return new static(new CarbonImmutable($string, $tz));
    }

    /**
     * @param string|CarbonTimeZone $tz
     *
     * @retrun static
     */
    public static function now($tz = self::TZ): self
    {
        return new static(new CarbonImmutable('now', $tz));
    }

    /**
     * @return static
     */
    public static function fromDateTime(\DateTimeInterface $date): self
    {
        return new static(CarbonImmutable::instance($date));
    }

    protected function __construct(CarbonImmutable $date)
    {
        $this->date = $date->startOfDay();
    }

    public function date(): CarbonImmutable
    {
        return $this->date;
    }

    public function format(string $format): string
    {
        return $this->date->format($format);
    }

    public function toString(): string
    {
        return $this->format(self::STRING_FORMAT);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Compares up to milliseconds. Ignores microseconds.
     *
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (static::class !== $other::class) {
            return false;
        }

        // diffInMilliseconds returns a float, so this will ignore microseconds
        return 0 == $this->date->diffInMilliseconds($other->date);
    }
}
