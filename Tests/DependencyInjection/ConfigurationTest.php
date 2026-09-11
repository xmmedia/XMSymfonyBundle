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

    private function process(array $config): array
    {
        return (new Processor())->processConfiguration(new Configuration(), [$config]);
    }
}
