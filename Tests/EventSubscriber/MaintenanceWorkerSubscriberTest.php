<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\EventSubscriber;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleSignalEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;
use Symfony\Component\Messenger\EventListener\StopWorkerOnRestartSignalListener;
use Symfony\Component\Messenger\Worker;
use Xm\SymfonyBundle\EventSubscriber\MaintenanceWorkerSubscriber;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceMode;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class MaintenanceWorkerSubscriberTest extends BaseTestCase
{
    private const string START = '2026-01-01 00:00:00';

    public function testSubscribedEvents(): void
    {
        $subscribed = MaintenanceWorkerSubscriber::getSubscribedEvents();

        $this->assertSame('onWorkerStarted', $subscribed[WorkerStartedEvent::class]);
        $this->assertSame('onWorkerRunning', $subscribed[WorkerRunningEvent::class]);
        $this->assertSame('onSignal', $subscribed[ConsoleEvents::SIGNAL]);
    }

    public function testCarriesOnWhenOff(): void
    {
        $clock = new MockClock(self::START);
        $worker = \Mockery::mock(Worker::class);
        $worker->shouldNotReceive('stop');

        $subscriber = new MaintenanceWorkerSubscriber($this->maintenanceMode(false), $clock);
        $subscriber->onWorkerStarted(new WorkerStartedEvent($worker));
        $subscriber->onWorkerRunning(new WorkerRunningEvent($worker, true));

        $this->assertSame(0, $this->secondsSlept($clock));
    }

    public function testPausesUntilOff(): void
    {
        $clock = new MockClock(self::START);
        $worker = \Mockery::mock(Worker::class);
        $worker->shouldNotReceive('stop');

        (new MaintenanceWorkerSubscriber($this->maintenanceMode(true, true, true, false), $clock))
            ->onWorkerRunning(new WorkerRunningEvent($worker, false));

        $this->assertSame(2 * MaintenanceWorkerSubscriber::CHECK_INTERVAL, $this->secondsSlept($clock));
    }

    public function testPausesWhenStarted(): void
    {
        $clock = new MockClock(self::START);
        $worker = \Mockery::mock(Worker::class);
        $worker->shouldNotReceive('stop');

        (new MaintenanceWorkerSubscriber($this->maintenanceMode(true, true, false), $clock))
            ->onWorkerStarted(new WorkerStartedEvent($worker));

        $this->assertSame(MaintenanceWorkerSubscriber::CHECK_INTERVAL, $this->secondsSlept($clock));
    }

    public function testStopsOnSignalWhilePaused(): void
    {
        $worker = \Mockery::mock(Worker::class);
        $worker->shouldReceive('stop')->once();

        $maintenanceMode = \Mockery::mock(MaintenanceMode::class);
        $subscriber = new MaintenanceWorkerSubscriber($maintenanceMode, new MockClock(self::START));

        $checks = 0;
        $maintenanceMode->shouldReceive('isOn')
            ->andReturnUsing(function () use ($subscriber, &$checks): bool {
                // the signal arrives while it's sleeping
                if (3 === ++$checks) {
                    $subscriber->onSignal($this->signalEvent(\SIGTERM));
                }

                return true;
            });

        $subscriber->onWorkerRunning(new WorkerRunningEvent($worker, true));

        $this->assertSame(3, $checks);
    }

    public function testKeepaliveSignalDoesntStop(): void
    {
        $clock = new MockClock(self::START);
        $worker = \Mockery::mock(Worker::class);
        $worker->shouldNotReceive('stop');

        $subscriber = new MaintenanceWorkerSubscriber($this->maintenanceMode(true, true, false), $clock);
        $subscriber->onSignal($this->signalEvent(\SIGALRM));
        $subscriber->onWorkerRunning(new WorkerRunningEvent($worker, true));

        $this->assertSame(MaintenanceWorkerSubscriber::CHECK_INTERVAL, $this->secondsSlept($clock));
    }

    public function testStopsOnRestartRequestedWhilePaused(): void
    {
        $worker = \Mockery::mock(Worker::class);
        $worker->shouldReceive('stop')->once();

        $restartSignal = new ArrayAdapter();
        // off when it starts, then on
        $subscriber = new MaintenanceWorkerSubscriber(
            $this->maintenanceMode(false, true, true),
            new MockClock(self::START),
            $restartSignal,
        );
        $subscriber->onWorkerStarted(new WorkerStartedEvent($worker));

        $restartSignal->save(
            $restartSignal->getItem(StopWorkerOnRestartSignalListener::RESTART_REQUESTED_TIMESTAMP_KEY)
                ->set(microtime(true) + 1),
        );

        $subscriber->onWorkerRunning(new WorkerRunningEvent($worker, true));
    }

    public function testRestartRequestedBeforeItStartedIsIgnored(): void
    {
        $clock = new MockClock(self::START);
        $worker = \Mockery::mock(Worker::class);
        $worker->shouldNotReceive('stop');

        $restartSignal = new ArrayAdapter();
        $restartSignal->save(
            $restartSignal->getItem(StopWorkerOnRestartSignalListener::RESTART_REQUESTED_TIMESTAMP_KEY)
                ->set(microtime(true) - 60),
        );

        (new MaintenanceWorkerSubscriber($this->maintenanceMode(true, true, false), $clock, $restartSignal))
            ->onWorkerStarted(new WorkerStartedEvent($worker));

        $this->assertSame(MaintenanceWorkerSubscriber::CHECK_INTERVAL, $this->secondsSlept($clock));
    }

    private function maintenanceMode(bool ...$isOn): MaintenanceMode
    {
        $maintenanceMode = \Mockery::mock(MaintenanceMode::class);
        $maintenanceMode->shouldReceive('isOn')->andReturnValues($isOn);

        return $maintenanceMode;
    }

    private function signalEvent(int $signal): ConsoleSignalEvent
    {
        return new ConsoleSignalEvent(new Command('messenger:consume'), new ArrayInput([]), new NullOutput(), $signal);
    }

    private function secondsSlept(MockClock $clock): int
    {
        return $clock->now()->getTimestamp() - (new \DateTimeImmutable(self::START))->getTimestamp();
    }
}
