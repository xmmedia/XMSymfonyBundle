<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Query;

use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupCollectionInterface;
use Symfony\WebpackEncoreBundle\Asset\EntrypointLookupInterface;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Query\EntrypointIntegrityHashQuery;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class EntrypointIntegrityHashQueryTest extends BaseTestCase
{
    public function testInvokeReturnsHashForValidEntrypoint(): void
    {
        $entrypoint = 'app';
        $filePath = '/build/app.js';
        $expectedHash = 'sha384-abc123';
        $integrityData = [
            $filePath => $expectedHash,
        ];

        $lookup = \Mockery::mock(EntrypointLookupInterface::class);
        $lookup->shouldReceive('getJavaScriptFiles')
            ->once()
            ->with($entrypoint)
            ->andReturn([$filePath]);
        $lookup->shouldReceive('getIntegrityData')
            ->once()
            ->andReturn($integrityData);

        $collection = \Mockery::mock(EntrypointLookupCollectionInterface::class);
        $collection->shouldReceive('getEntrypointLookup')
            ->once()
            ->andReturn($lookup);

        $query = new EntrypointIntegrityHashQuery($collection);
        $result = $query($entrypoint);

        $this->assertEquals($expectedHash, $result);
    }

    public function testInvokeReturnsNullWhenNoIntegrityData(): void
    {
        $entrypoint = 'app';
        $filePath = '/build/app.js';

        $lookup = \Mockery::mock(EntrypointLookupInterface::class);
        $lookup->shouldReceive('getJavaScriptFiles')
            ->once()
            ->with($entrypoint)
            ->andReturn([$filePath]);
        $lookup->shouldReceive('getIntegrityData')
            ->once()
            ->andReturn([]);

        $collection = \Mockery::mock(EntrypointLookupCollectionInterface::class);
        $collection->shouldReceive('getEntrypointLookup')
            ->once()
            ->andReturn($lookup);

        $query = new EntrypointIntegrityHashQuery($collection);
        $result = $query($entrypoint);

        $this->assertNull($result);
    }

    public function testInvokeUsesFirstFileFromArray(): void
    {
        $entrypoint = 'app';
        $files = ['/build/app.js', '/build/vendor.js'];
        $expectedHash = 'sha384-xyz789';
        $integrityData = [
            $files[0] => $expectedHash,
            $files[1] => 'sha384-other',
        ];

        $lookup = \Mockery::mock(EntrypointLookupInterface::class);
        $lookup->shouldReceive('getJavaScriptFiles')
            ->once()
            ->with($entrypoint)
            ->andReturn($files);
        $lookup->shouldReceive('getIntegrityData')
            ->once()
            ->andReturn($integrityData);

        $collection = \Mockery::mock(EntrypointLookupCollectionInterface::class);
        $collection->shouldReceive('getEntrypointLookup')
            ->once()
            ->andReturn($lookup);

        $query = new EntrypointIntegrityHashQuery($collection);
        $result = $query($entrypoint);

        $this->assertEquals($expectedHash, $result);
    }

    public function testConstructorWithNullCollection(): void
    {
        $query = new EntrypointIntegrityHashQuery(null);

        $this->assertInstanceOf(EntrypointIntegrityHashQuery::class, $query);
    }
}
