<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Carbon\CarbonImmutable;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

trait EventCreatedAtSetter
{
    private function setEventCreatedAt(AggregateChanged $event, CarbonImmutable $date): void
    {
        $reflection = new \ReflectionClass($event);
        $property = $reflection->getProperty('createdAt');
        $property->setValue($event, $date->toDateTimeImmutable());
    }
}
