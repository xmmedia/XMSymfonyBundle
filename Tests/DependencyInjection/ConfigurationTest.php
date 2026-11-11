<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
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

    public function testSentryScrubberDisabledByDefault(): void
    {
        $this->assertSame(
            ['enabled' => false, 'paths' => [], 'query_params' => [], 'patterns' => []],
            $this->process([])['sentry_scrubber'],
        );
    }

    public function testSentryScrubberConfigured(): void
    {
        $scrubber = $this->process(['sentry_scrubber' => [
            'paths'        => ['activate' => ['prefix' => '/activate/']],
            'query_params' => ['login_link' => ['path' => '/login-link', 'param' => 'hash']],
            'patterns'     => ['api_key' => ['regex' => '~api_key: \\w+~', 'replacement' => 'api_key: x']],
        ]])['sentry_scrubber'];

        $this->assertTrue($scrubber['enabled']);
        $this->assertSame(
            ['activate' => ['prefix' => '/activate/', 'segment' => null, 'replacement' => '[Filtered]']],
            $scrubber['paths'],
        );
        $this->assertSame(
            ['login_link' => ['path' => '/login-link', 'param' => 'hash', 'replacement' => '[Filtered]']],
            $scrubber['query_params'],
        );
        $this->assertSame(
            ['api_key' => ['regex' => '~api_key: \\w+~', 'replacement' => 'api_key: x']],
            $scrubber['patterns'],
        );
    }

    public function testSentryScrubberMergesConfigsByName(): void
    {
        $scrubber = (new Processor())->processConfiguration(new Configuration(), [
            ['sentry_scrubber' => ['paths' => ['activate' => ['prefix' => '/activate/']]]],
            ['sentry_scrubber' => ['paths' => ['client' => ['prefix' => '/client/', 'segment' => '[a-z]{43}']]]],
        ])['sentry_scrubber'];

        $this->assertSame(['activate', 'client'], array_keys($scrubber['paths']));
    }

    public function testSentryScrubberRejectsAnInvalidSegment(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->process(['sentry_scrubber' => ['paths' => ['bad' => ['prefix' => '/a/', 'segment' => '[a-z']]]]);
    }

    public function testSentryScrubberRejectsAnInvalidRegex(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->process(['sentry_scrubber' => ['patterns' => ['bad' => ['regex' => 'no delimiters']]]]);
    }

    private function process(array $config): array
    {
        return (new Processor())->processConfiguration(new Configuration(), [$config]);
    }
}
