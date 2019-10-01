<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait UuidId
{
    /** @var UuidInterface */
    private $uuid;

    public static function fromString(string $id): self
    {
        return new static(Uuid::fromString($id));
    }

    public static function fromUuid(UuidInterface $id): self
    {
        return new static($id);
    }

    protected function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }

    public function toString(): string
    {
        return $this->uuid->toString();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function uuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (\get_class($this) !== \get_class($other)) {
            return false;
        }

        return $this->uuid->equals($other->uuid);
    }
}
