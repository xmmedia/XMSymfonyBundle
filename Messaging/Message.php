<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Messaging;

use Prooph\Common\Messaging\Message as BaseMessage;
use Ramsey\Uuid\UuidInterface;

interface Message extends BaseMessage
{
    /**
     * Should be one of Message::TYPE_COMMAND, Message::TYPE_EVENT or Message::TYPE_QUERY.
     */
    #[\Override]
    public function messageType(): string;

    #[\Override]
    public function uuid(): UuidInterface;

    #[\Override]
    public function createdAt(): \DateTimeImmutable;

    #[\Override]
    public function payload(): array;

    #[\Override]
    public function metadata(): array;

    #[\Override]
    public function withMetadata(array $metadata): BaseMessage;

    /**
     * Returns new instance of message with $key => $value added to metadata.
     *
     * Given value must have a scalar or array type.
     */
    #[\Override]
    public function withAddedMetadata(string $key, $value): BaseMessage;
}
