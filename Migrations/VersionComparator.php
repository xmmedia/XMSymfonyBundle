<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Migrations;

use Doctrine\Migrations\Version\Comparator;
use Doctrine\Migrations\Version\Version;

/**
 * Orders migrations by class name, ignoring the namespace, so a project with migrations in more than one
 * namespace (eg DoctrineMigrations & App\Migrations) runs them in date order. Doctrine's default compares
 * the full class name, so every migration in one namespace runs before any in the next.
 */
final class VersionComparator implements Comparator
{
    public function compare(Version $a, Version $b): int
    {
        return strcmp(self::className($a), self::className($b)) ?: strcmp((string) $a, (string) $b);
    }

    private static function className(Version $version): string
    {
        return substr((string) strrchr('\\'.$version, '\\'), 1);
    }
}
