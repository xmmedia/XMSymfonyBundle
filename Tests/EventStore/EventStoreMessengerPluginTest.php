<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\EventStore;

use Symfony\Component\Messenger\MessageBusInterface;
use Xm\SymfonyBundle\EventStore\EventStoreMessengerPlugin;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class EventStoreMessengerPluginTest extends BaseTestCase
{
    public function testPluginExtendsAbstractPlugin(): void
    {
        $eventBus = \Mockery::mock(MessageBusInterface::class);
        $plugin = new EventStoreMessengerPlugin($eventBus);

        $this->assertInstanceOf(\Prooph\EventStore\Plugin\AbstractPlugin::class, $plugin);
    }

    public function testAttachToEventStoreMethodExists(): void
    {
        $eventBus = \Mockery::mock(MessageBusInterface::class);
        $plugin = new EventStoreMessengerPlugin($eventBus);

        $this->assertTrue(method_exists($plugin, 'attachToEventStore'));
    }
}
