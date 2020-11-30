<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Util;

class Utils
{
    /**
     * If object is being serialized, it must have a
     * __toString, getValue, or toArray method.
     *
     * @param mixed $var
     *
     * @return mixed
     */
    public static function serialize($var)
    {
        if (null === $var) {
            return null;
        }

        if (is_scalar($var) || \is_array($var)) {
            return $var;
        }

        if (\is_object($var) && is_callable([$var, 'getValue'])) {
            return $var->getValue();
        }

        if (\is_object($var) && is_callable([$var, '__toString'])) {
            return (string) $var->__toString();
        }

        if (\is_object($var) && is_callable([$var, 'toArray'])) {
            return $var->toArray();
        }

        throw new \InvalidArgumentException(sprintf('Can\'t serialize an %s.', self::printSafe($var)));
    }

    /**
     * @param mixed $var
     */
    public static function printSafe($var): string
    {
        if (\is_object($var)) {
            return 'instance of '.\get_class($var);
        }
        if (\is_array($var)) {
            return 'array';
        }
        if ('' === $var) {
            return '(empty string)';
        }
        if (null === $var) {
            return 'NULL';
        }
        if (false === $var) {
            return 'false (boolean)';
        }
        if (true === $var) {
            return 'true (boolean)';
        }
        if (\is_string($var)) {
            return $var;
        }
        if (is_scalar($var)) {
            return (string) $var;
        }

        return \gettype($var);
    }
}
