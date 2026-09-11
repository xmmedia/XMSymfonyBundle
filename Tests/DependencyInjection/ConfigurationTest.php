<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Xm\SymfonyBundle\DependencyInjection\Configuration;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class ConfigurationTest extends BaseTestCase
{
    public function testSessionExpiryEnabledByDefault(): void
    {
        $this->assertSame(['enabled' => true], $this->process([])['session_expiry']);
    }

    public function testSessionExpiryDisabled(): void
    {
        $this->assertSame(['enabled' => false], $this->process(['session_expiry' => false])['session_expiry']);
        $this->assertSame(
            ['enabled' => false],
            $this->process(['session_expiry' => ['enabled' => false]])['session_expiry'],
        );
    }

    public function testMaintenanceEnabledByDefault(): void
    {
        $this->assertSame(
            ['enabled' => true, 'file' => '%kernel.project_dir%/var/maintenance', 'time_zone' => null],
            $this->process([])['maintenance'],
        );
    }

    public function testMaintenanceConfigured(): void
    {
        $maintenance = $this->process([
            'maintenance' => ['file' => '/tmp/maintenance', 'time_zone' => 'America/Edmonton'],
        ])['maintenance'];

        $this->assertTrue($maintenance['enabled']);
        $this->assertSame('/tmp/maintenance', $maintenance['file']);
        $this->assertSame('America/Edmonton', $maintenance['time_zone']);
    }

    public function testMaintenanceDisabled(): void
    {
        $this->assertFalse($this->process(['maintenance' => false])['maintenance']['enabled']);
    }

    private function process(array $config): array
    {
        return (new Processor())->processConfiguration(new Configuration(), [$config]);
    }
}
