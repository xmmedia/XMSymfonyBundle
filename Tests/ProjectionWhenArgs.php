<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

trait ProjectionWhenArgs
{
    private function whenArgs(array $projectedEvents): callable
    {
        return function ($eventHandlers) use ($projectedEvents): bool {
            if (!\is_array($eventHandlers)) {
                return false;
            }

            // make sure all events in list are used
            foreach ($projectedEvents as $event) {
                if (!\array_key_exists($event, $eventHandlers)) {
                    return false;
                }
            }

            // make sure there are not extra events
            foreach ($eventHandlers as $event => $handler) {
                if (false === array_search($event, $projectedEvents)) {
                    return false;
                }
            }

            return true;
        };
    }
}
