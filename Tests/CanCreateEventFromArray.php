<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Faker;
use Xm\SymfonyBundle\Messaging\DomainEvent;

trait CanCreateEventFromArray
{
    protected function createEventFromArray(
        string $eventName,
        string $aggregateId,
        array $payload = []
    ): DomainEvent {
        $faker = Faker\Factory::create();

        return $eventName::fromArray([
            'message_name' => $eventName,
            'uuid'         => method_exists($faker, 'uuid3') ? $faker->uuid3() : $faker->uuid(),
            'payload'      => $payload,
            'metadata'     => [
                '_aggregate_id' => $aggregateId,
            ],
            'created_at'   => new \DateTimeImmutable(),
        ]);
    }
}
