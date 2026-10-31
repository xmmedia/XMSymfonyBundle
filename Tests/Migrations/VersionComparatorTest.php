<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Migrations;

use Doctrine\Migrations\Version\Version;
use Xm\SymfonyBundle\Migrations\VersionComparator;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class VersionComparatorTest extends BaseTestCase
{
    public function testOrdersByClassNameAcrossNamespaces(): void
    {
        $versions = [
            new Version('App\Migrations\Version20261029001'),
            new Version('DoctrineMigrations\Version20261028001'),
            new Version('App\Migrations\Version20261027001'),
            new Version('DoctrineMigrations\Version20261029002'),
        ];

        usort($versions, new VersionComparator()->compare(...));

        $this->assertSame(
            [
                'App\Migrations\Version20261027001',
                'DoctrineMigrations\Version20261028001',
                'App\Migrations\Version20261029001',
                'DoctrineMigrations\Version20261029002',
            ],
            array_map(strval(...), $versions),
        );
    }

    public function testSameClassNameFallsBackToNamespace(): void
    {
        $comparator = new VersionComparator();

        $this->assertLessThan(
            0,
            $comparator->compare(
                new Version('App\Migrations\Version20261029001'),
                new Version('DoctrineMigrations\Version20261029001'),
            ),
        );
    }

    public function testVersionWithoutNamespace(): void
    {
        $this->assertLessThan(
            0,
            new VersionComparator()->compare(
                new Version('Version20261028001'),
                new Version('App\Migrations\Version20261029001'),
            ),
        );
    }
}
