<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSourcing\Aggregate;

use Xm\SymfonyBundle\EventSourcing\AggregateChanged;

trait EventSourcedTrait
{
    /**
     * Current version.
     *
     * @var int
     */
    protected $version = 0;

    protected static function reconstituteFromHistory(\Iterator $historyEvents): self
    {
        $instance = new static();
        $instance->replay($historyEvents);

        return $instance;
    }

    /**
     * Replay past events.
     */
    protected function replay(\Iterator $historyEvents): void
    {
        foreach ($historyEvents as $pastEvent) {
            /* @var AggregateChanged $pastEvent */
            $this->version = $pastEvent->version();

            $this->apply($pastEvent);
        }
    }

    abstract protected function aggregateId(): string;

    /**
     * Apply given event.
     */
    abstract protected function apply(AggregateChanged $event): void;
}
