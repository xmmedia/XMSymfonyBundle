<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use Money\Currency;
use Money\Money;

final class MoneyFactory
{
    private static string $currency = 'CAD';

    public static function fromInt(int $cents): Money
    {
        return new Money($cents, new Currency(self::$currency));
    }

    public static function fromFloat(float $cents): Money
    {
        return new Money((int) round($cents), new Currency(self::$currency));
    }

    public static function fromString(string $cents): Money
    {
        return new Money($cents, new Currency(self::$currency));
    }

    public static function zero(): Money
    {
        return self::fromInt(0);
    }
}
