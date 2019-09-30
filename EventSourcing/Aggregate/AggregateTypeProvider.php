<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSourcing\Aggregate;

interface AggregateTypeProvider
{
    public function aggregateType(): AggregateType;
}
