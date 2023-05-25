<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Util;

class Json
{
    /**
     * @throws \JsonException
     */
    public static function encode(mixed $value): string
    {
        $options = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION | \JSON_THROW_ON_ERROR;

        return json_encode($value, $options);
    }

    /**
     * @throws \JsonException
     */
    public static function decode(string $json): mixed
    {
        return json_decode($json, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);
    }
}
