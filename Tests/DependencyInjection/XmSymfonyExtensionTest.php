<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Xm\SymfonyBundle\Command\MaintenanceCommand;
use Xm\SymfonyBundle\DependencyInjection\XmSymfonyExtension;
use Xm\SymfonyBundle\EventSubscriber\MaintenanceSubscriber;
use Xm\SymfonyBundle\EventSubscriber\MaintenanceWorkerSubscriber;
use Xm\SymfonyBundle\EventSubscriber\SessionExpirySubscriber;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceGate;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceMode;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenancePage;
use Xm\SymfonyBundle\Security\SessionExpiry;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class XmSymfonyExtensionTest extends BaseTestCase
{
    public function testSessionExpiryEnabledByDefault(): void
    {
        $container = $this->load([]);

        $this->assertTrue($container->getDefinition(SessionExpiry::class)->isAutowired());

        $subscriber = $container->getDefinition(SessionExpirySubscriber::class);
        $this->assertTrue($subscriber->isAutowired());
        $this->assertTrue($subscriber->isAutoconfigured());
    }

    public function testSessionExpiryDisabled(): void
    {
        $container = $this->load(['session_expiry' => false]);

        $this->assertFalse($container->hasDefinition(SessionExpiry::class));
        $this->assertFalse($container->hasDefinition(SessionExpirySubscriber::class));
    }

    public function testMaintenanceEnabledByDefault(): void
    {
        $container = $this->load([]);

        $this->assertSame(
            '%kernel.project_dir%/var/maintenance',
            $container->getDefinition(MaintenanceMode::class)->getArgument('$file'),
        );

        foreach ([MaintenanceSubscriber::class, MaintenanceWorkerSubscriber::class, MaintenanceCommand::class] as $id) {
            $definition = $container->getDefinition($id);
            $this->assertTrue($definition->isAutowired());
            $this->assertTrue($definition->isAutoconfigured());
        }

        $this->assertTrue($container->hasDefinition(MaintenanceGate::class));
        $this->assertNull($container->getDefinition(MaintenancePage::class)->getArgument('$timeZone'));
    }

    public function testMaintenanceConfigured(): void
    {
        $container = $this->load([
            'maintenance' => ['file' => '/tmp/maintenance', 'time_zone' => 'America/Edmonton'],
        ]);

        $this->assertSame('/tmp/maintenance', $container->getDefinition(MaintenanceMode::class)->getArgument('$file'));
        $this->assertSame(
            'America/Edmonton',
            $container->getDefinition(MaintenancePage::class)->getArgument('$timeZone'),
        );
        $this->assertSame(
            'America/Edmonton',
            $container->getDefinition(MaintenanceCommand::class)->getArgument('$timeZone'),
        );
    }

    public function testMaintenanceDisabled(): void
    {
        $container = $this->load(['maintenance' => false]);

        $this->assertFalse($container->hasDefinition(MaintenanceMode::class));
        $this->assertFalse($container->hasDefinition(MaintenanceSubscriber::class));
        $this->assertFalse($container->hasDefinition(MaintenanceGate::class));
        $this->assertFalse($container->hasDefinition(MaintenanceWorkerSubscriber::class));
        $this->assertFalse($container->hasDefinition(MaintenanceCommand::class));
    }

    private function load(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();

        (new XmSymfonyExtension())->load([$config], $container);

        return $container;
    }
}
