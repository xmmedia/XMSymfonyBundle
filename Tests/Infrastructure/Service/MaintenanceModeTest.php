<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\Service;

use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceMode;
use Xm\SymfonyBundle\Infrastructure\Service\MaintenanceSettings;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class MaintenanceModeTest extends BaseTestCase
{
    private string $dir;
    private string $file;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir().'/maintenance_'.bin2hex(random_bytes(8));
        $this->file = $this->dir.'/maintenance';
    }

    protected function tearDown(): void
    {
        foreach ([$this->file, $this->file.'.html'] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        if (is_dir($this->dir)) {
            rmdir($this->dir);
        }

        parent::tearDown();
    }

    public function testOffWithoutTheFile(): void
    {
        $maintenanceMode = new MaintenanceMode($this->file);

        $this->assertFalse($maintenanceMode->isOn());
        $this->assertNull($maintenanceMode->settings());
        $this->assertFalse($maintenanceMode->disable());
    }

    public function testEnableWritesTheSettings(): void
    {
        $maintenanceMode = new MaintenanceMode($this->file);
        $message = $this->faker()->sentence();
        $ip = $this->faker()->ipv4();

        $maintenanceMode->enable(new MaintenanceSettings($message, null, [$ip]));

        $this->assertTrue($maintenanceMode->isOn());
        $this->assertSame($message, $maintenanceMode->settings()->message());
        $this->assertSame([$ip], $maintenanceMode->settings()->allowedIps());
        $this->assertNull($maintenanceMode->page());
    }

    public function testEnableWritesThePage(): void
    {
        $maintenanceMode = new MaintenanceMode($this->file);
        $page = $this->faker()->randomHtml();

        $maintenanceMode->enable(new MaintenanceSettings(), $page);

        $this->assertSame($page, $maintenanceMode->page());
        $this->assertFileExists($this->file.'.html');
    }

    public function testEnableWithoutAPageRemovesIt(): void
    {
        $maintenanceMode = new MaintenanceMode($this->file);
        $maintenanceMode->enable(new MaintenanceSettings(), $this->faker()->randomHtml());

        $maintenanceMode->enable(new MaintenanceSettings());

        $this->assertNull($maintenanceMode->page());
    }

    public function testEnableReplacesTheSettings(): void
    {
        $maintenanceMode = new MaintenanceMode($this->file);
        $message = $this->faker()->sentence();

        $maintenanceMode->enable(new MaintenanceSettings($this->faker()->sentence()));
        $maintenanceMode->enable(new MaintenanceSettings($message));

        $this->assertSame($message, $maintenanceMode->settings()->message());
    }

    public function testEmptyFileIsOnWithDefaults(): void
    {
        mkdir($this->dir);
        touch($this->file);

        $settings = (new MaintenanceMode($this->file))->settings();

        $this->assertNotNull($settings);
        $this->assertSame(MaintenanceSettings::DEFAULT_MESSAGE, $settings->displayMessage());
    }

    public function testDisable(): void
    {
        $maintenanceMode = new MaintenanceMode($this->file);
        $maintenanceMode->enable(new MaintenanceSettings(), $this->faker()->randomHtml());

        $this->assertTrue($maintenanceMode->disable());
        $this->assertFalse($maintenanceMode->isOn());
        $this->assertFileDoesNotExist($this->file);
        $this->assertFileDoesNotExist($this->file.'.html');
    }

    public function testNoticesTheFileChanging(): void
    {
        $maintenanceMode = new MaintenanceMode($this->file);
        $this->assertFalse($maintenanceMode->isOn());

        mkdir($this->dir);
        touch($this->file);
        $this->assertTrue($maintenanceMode->isOn());

        unlink($this->file);
        $this->assertFalse($maintenanceMode->isOn());
    }
}
