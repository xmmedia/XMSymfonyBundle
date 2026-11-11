<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Command;

/**
 * @internal
 */
trait ProjectionNameTrait
{
    /**
     * Projection names end in "_projection", which can be left off on the command line.
     */
    private static function projectionName(string $name): string
    {
        return str_ends_with($name, '_projection') ? $name : $name.'_projection';
    }
}
