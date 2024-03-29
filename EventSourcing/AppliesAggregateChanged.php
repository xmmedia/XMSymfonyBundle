<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSourcing;

trait AppliesAggregateChanged
{
    protected function apply(AggregateChanged $e): void
    {
        $handler = $this->determineEventHandlerMethodFor($e);
        if (!method_exists($this, $handler)) {
            throw new \RuntimeException(sprintf('Missing event handler method %s on aggregate root %s', $handler, static::class));
        }

        $this->{$handler}($e);
    }

    protected function determineEventHandlerMethodFor(AggregateChanged $e): string
    {
        return 'when'.implode('', \array_slice(explode('\\', $e::class), -1));
    }
}
