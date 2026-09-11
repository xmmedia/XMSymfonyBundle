<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\EventSubscriber;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleSignalEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;
use Symfony\Component\Messenger\EventListener\StopWorkerOnRestartSignalListener;
use Symfony\Component\Messenger\Worker;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceMode;

/**
 * Pauses messenger workers while maintenance mode is on: when they start & between messages,
 * so a message is never picked up part way through.
 *
 * They wait inside the worker rather than exiting, so whatever restarts them (supervisor,
 * systemd) doesn't restart them in a tight loop. A stop signal or messenger:stop-workers still
 * stops them while paused – the worker can't check for either until it's handed back control.
 */
final class MaintenanceWorkerSubscriber implements EventSubscriberInterface
{
    // seconds between checks while paused
    public const int CHECK_INTERVAL = 5;

    private float $workerStartedAt = 0;
    private bool $stopRequested = false;

    /**
     * @param CacheItemPoolInterface|null $restartSignalCachePool where messenger:stop-workers records its signal
     */
    public function __construct(
        private readonly MaintenanceMode $maintenanceMode,
        private readonly ClockInterface $clock,
        private readonly ?CacheItemPoolInterface $restartSignalCachePool = null,
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerStartedEvent::class => 'onWorkerStarted',
            WorkerRunningEvent::class => 'onWorkerRunning',
            ConsoleEvents::SIGNAL     => 'onSignal',
        ];
    }

    public function onWorkerStarted(WorkerStartedEvent $event): void
    {
        $this->workerStartedAt = microtime(true);

        $this->pause($event->getWorker());
    }

    public function onWorkerRunning(WorkerRunningEvent $event): void
    {
        $this->pause($event->getWorker());
    }

    /**
     * The same signals messenger:consume stops on. SIGALRM is its keepalive.
     */
    public function onSignal(ConsoleSignalEvent $event): void
    {
        if (\SIGALRM !== $event->getHandlingSignal()) {
            $this->stopRequested = true;
        }
    }

    private function pause(Worker $worker): void
    {
        if (!$this->maintenanceMode->isOn()) {
            return;
        }

        $this->logger->info('Worker paused for maintenance.');

        while ($this->maintenanceMode->isOn()) {
            if ($this->stopRequested || $this->restartRequested()) {
                $worker->stop();
                $this->logger->info('Worker stopped while paused for maintenance.');

                return;
            }

            $this->clock->sleep(self::CHECK_INTERVAL);
        }

        $this->logger->info('Worker resumed after maintenance.');
    }

    /**
     * Same check as StopWorkerOnRestartSignalListener, which can't run while the worker's paused.
     */
    private function restartRequested(): bool
    {
        if (null === $this->restartSignalCachePool) {
            return false;
        }

        $cacheItem = $this->restartSignalCachePool->getItem(
            StopWorkerOnRestartSignalListener::RESTART_REQUESTED_TIMESTAMP_KEY,
        );

        return $cacheItem->isHit() && $this->workerStartedAt < $cacheItem->get();
    }
}
