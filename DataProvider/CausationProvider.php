<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataProvider;

use Ramsey\Uuid\UuidInterface;

class CausationProvider
{
    private UuidInterface|null $causationId = null;

    public function storeCausationId(UuidInterface $uuid): void
    {
        $this->causationId = $uuid;
    }

    public function retrieveCausationId(): ?UuidInterface
    {
        return $this->causationId;
    }
}
