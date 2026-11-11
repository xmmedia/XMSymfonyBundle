<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Messaging;

use PHPUnit\Framework\Attributes\DataProvider;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\InvalidArgumentException;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeCommand;
use Xm\SymfonyBundle\Tests\FakeEvent;

class DomainMessageTest extends BaseTestCase
{
    public function testFromArray(): void
    {
        $data = $this->messageData(FakeCommand::class);

        $message = FakeCommand::fromArray($data);

        $this->assertInstanceOf(FakeCommand::class, $message);
        $this->assertSame($data['uuid'], $message->uuid()->toString());
        $this->assertSame(FakeCommand::class, $message->messageName());
        $this->assertSame($data['payload'], $message->payload());
        $this->assertSame($data['metadata'], $message->metadata());
        $this->assertSame($data['created_at'], $message->createdAt());
    }

    public function testFromArrayEvent(): void
    {
        $data = $this->messageData(FakeEvent::class);
        $data['metadata']['_aggregate_id'] = Uuid::uuid4()->toString();

        $event = FakeEvent::fromArray($data);

        $this->assertInstanceOf(FakeEvent::class, $event);
        $this->assertSame($data['metadata']['_aggregate_id'], $event->aggregateId());
    }

    public function testToArrayRoundTrip(): void
    {
        $command = FakeCommand::create(['name' => 'Jane', 'nested' => ['a' => 1, 'b' => null]])
            ->withAddedMetadata('key', 'value');

        $result = FakeCommand::fromArray($command->toArray());

        $this->assertSame($command->toArray(), $result->toArray());
    }

    #[DataProvider('invalidDataProvider')]
    public function testFromArrayInvalid(callable $change, string $message): void
    {
        $data = $this->messageData(FakeCommand::class);
        $change($data);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains($message);

        FakeCommand::fromArray($data);
    }

    public static function invalidDataProvider(): \Generator
    {
        yield 'missing uuid' => [
            static function (array &$data): void {
                unset($data['uuid']);
            },
            'MessageData must contain a key uuid',
        ];
        yield 'invalid uuid' => [
            static function (array &$data): void {
                $data['uuid'] = 'not-a-uuid';
            },
            'uuid must be a valid UUID string',
        ];
        yield 'short message name' => [
            static function (array &$data): void {
                $data['message_name'] = 'ab';
            },
            'message_name must be string with at least 3 chars length',
        ];
        yield 'object in payload' => [
            static function (array &$data): void {
                $data['payload'] = ['a' => new \stdClass()];
            },
            'payload must only contain arrays and scalar values',
        ];
        yield 'array in metadata' => [
            static function (array &$data): void {
                $data['metadata'] = ['a' => []];
            },
            'A metadata value must have a scalar type.',
        ];
        yield 'created at not immutable' => [
            static function (array &$data): void {
                $data['created_at'] = new \DateTime();
            },
            'created_at must be of type',
        ];
    }

    private function messageData(string $messageName): array
    {
        return [
            'message_name' => $messageName,
            'uuid'         => Uuid::uuid4()->toString(),
            'payload'      => ['name' => 'Jane'],
            'metadata'     => ['key' => 'value'],
            'created_at'   => new \DateTimeImmutable('2023-06-20 10:11:12.123456'),
        ];
    }
}
