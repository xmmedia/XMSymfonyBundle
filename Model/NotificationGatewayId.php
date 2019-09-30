<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model;

use Webmozart\Assert\Assert;

class NotificationGatewayId implements ValueObject
{
    /** @var string */
    private $id;

    public static function fromString(string $id): self
    {
        Assert::notEmpty($id, 'The ID cannot be empty.');

        return new static($id);
    }

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public function toString(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @param self|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        if (\get_class($this) !== \get_class($other)) {
            return false;
        }

        return $this->id === $other->id;
    }
}
