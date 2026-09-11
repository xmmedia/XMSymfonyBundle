<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Xm\SymfonyBundle\DependencyInjection\XmSymfonyExtension;
use Xm\SymfonyBundle\EventSubscriber\SessionExpirySubscriber;
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

    private function load(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();

        (new XmSymfonyExtension())->load([$config], $container);

        return $container;
    }
}
