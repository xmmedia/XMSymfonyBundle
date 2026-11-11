<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Exception;

final class InvalidCountry extends \InvalidArgumentException
{
    public static function invalid(string $country): self
    {
        return new self(\sprintf('The country "%s" is invalid.', $country));
    }
}
