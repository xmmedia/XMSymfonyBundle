<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Messaging;

use Prooph\Common\Messaging\DomainMessage as BaseDomainMessage;
use Prooph\Common\Messaging\Message as BaseMessage;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * Base class for commands and domain events.
 * All are messages but differ in their intention.
 */
abstract class DomainMessage extends BaseDomainMessage implements Message
{
    /**
     * @var string
     */
    protected $messageName;

    /**
     * @var UuidInterface
     */
    protected $uuid;

    /**
     * @var \DateTimeImmutable
     */
    protected $createdAt;

    /**
     * @var array
     */
    protected $metadata = [];

    /**
     * @param array<string, mixed> $payload
     */
    #[\Override]
    abstract protected function setPayload(array $payload): void;

    #[\Override]
    public static function fromArray(array $messageData): BaseDomainMessage
    {
        MessageDataAssertion::assert($messageData);

        $messageRef = new \ReflectionClass(static::class);

        /** @var DomainMessage $message */
        $message = $messageRef->newInstanceWithoutConstructor();

        $message->uuid = Uuid::fromString($messageData['uuid']);
        $message->messageName = $messageData['message_name'];
        $message->metadata = $messageData['metadata'];
        $message->createdAt = $messageData['created_at'];
        $message->setPayload($messageData['payload']);

        return $message;
    }

    #[\Override]
    protected function init(): void
    {
        if (null === $this->uuid) {
            $this->uuid = Uuid::uuid4();
        }

        if (null === $this->messageName) {
            $this->messageName = static::class;
        }

        if (null === $this->createdAt) {
            $this->createdAt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        }
    }

    #[\Override]
    public function uuid(): UuidInterface
    {
        return $this->uuid;
    }

    #[\Override]
    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    #[\Override]
    public function metadata(): array
    {
        return $this->metadata;
    }

    #[\Override]
    public function toArray(): array
    {
        return [
            'message_name' => $this->messageName,
            'uuid'         => $this->uuid->toString(),
            'payload'      => $this->payload(),
            'metadata'     => $this->metadata,
            'created_at'   => $this->createdAt(),
        ];
    }

    #[\Override]
    public function messageName(): string
    {
        return $this->messageName;
    }

    #[\Override]
    public function withMetadata(array $metadata): BaseMessage
    {
        $message = clone $this;

        $message->metadata = $metadata;

        return $message;
    }

    /**
     * Returns new instance of message with $key => $value added to metadata.
     *
     * Given value must have a scalar type.
     */
    #[\Override]
    public function withAddedMetadata(string $key, $value): BaseMessage
    {
        Assert::notEmpty($key, 'Invalid key');

        $message = clone $this;

        $message->metadata[$key] = $value;

        return $message;
    }
}
