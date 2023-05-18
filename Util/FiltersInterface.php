<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Util;

interface FiltersInterface
{
    /**
     * @return static
     */
    public static function fromArray(?array $filters): self;

    public function applied(string $field): bool;

    public function get(string $field): mixed;

    public function toArray(): array;
}
