<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use Ramsey\Uuid\Uuid;

/**
 * [!!] Avoid using unless necessary.
 */
trait UuidIdGeneratable
{
    /**
     * @return static
     */
    public static function generate(): self
    {
        return new static(Uuid::uuid4());
    }
}
