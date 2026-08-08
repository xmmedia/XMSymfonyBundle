<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Util;

interface FiltersInterface
{
    public static function fromArray(?array $filters): self;

    public function applied(string $field): bool;

    public function get(string $field): mixed;

    public function isTrue(mixed $value): bool;

    public function toArray(): array;
}
