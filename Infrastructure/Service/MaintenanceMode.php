<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Infrastructure\Service;

use Symfony\Component\Filesystem\Filesystem;

/**
 * Maintenance mode is on while the maintenance file (xm_symfony.maintenance.file) exists. By
 * default it's in var/, which is shared between releases, so it can be turned on & off without
 * deploying – with app:maintenance, or a bare `touch` if the app won't boot.
 */
class MaintenanceMode
{
    private readonly Filesystem $filesystem;

    public function __construct(private readonly string $file)
    {
        $this->filesystem = new Filesystem();
    }

    public function isOn(): bool
    {
        // checked repeatedly by paused workers, so PHP's cached result can't be used
        clearstatcache(true, $this->file);

        return is_file($this->file);
    }

    /**
     * @return MaintenanceSettings|null null when maintenance mode is off
     */
    public function settings(): ?MaintenanceSettings
    {
        if (!$this->isOn()) {
            return null;
        }

        $contents = @file_get_contents($this->file);

        // turned off since it was checked
        if (false === $contents) {
            return null;
        }

        return MaintenanceSettings::fromJson($contents);
    }

    /**
     * Turns it on, or updates the settings if it's already on. The file is replaced atomically,
     * so a request never reads half of it.
     */
    public function enable(MaintenanceSettings $settings): void
    {
        $this->filesystem->dumpFile(
            $this->file,
            json_encode($settings, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @return bool false if it was already off
     */
    public function disable(): bool
    {
        if (!$this->isOn()) {
            return false;
        }

        $this->filesystem->remove($this->file);

        return true;
    }
}
