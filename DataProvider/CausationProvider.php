<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataProvider;

use Ramsey\Uuid\UuidInterface;
use Symfony\Contracts\Service\ResetInterface;

/**
 * Holds the ID of the command being handled, so it's added to the events it causes.
 * Reset between messenger messages (& requests in a long running process),
 * so an event recorded outside a command isn't given the previous command's ID.
 */
class CausationProvider implements ResetInterface
{
    private ?UuidInterface $causationId = null;

    public function storeCausationId(UuidInterface $uuid): void
    {
        $this->causationId = $uuid;
    }

    public function retrieveCausationId(): ?UuidInterface
    {
        return $this->causationId;
    }

    public function reset(): void
    {
        $this->causationId = null;
    }
}
