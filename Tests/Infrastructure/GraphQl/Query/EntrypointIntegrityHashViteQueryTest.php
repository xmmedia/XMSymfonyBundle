<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Query;

use Pentatrion\ViteBundle\Service\EntrypointsLookup;
use Pentatrion\ViteBundle\Service\EntrypointsLookupCollection;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Query\EntrypointIntegrityHashViteQuery;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class EntrypointIntegrityHashViteQueryTest extends BaseTestCase
{
    public function testInvokeReturnsHashForValidEntrypoint(): void
    {
        $entrypoint = 'app';
        $fileData = ['app.js'];
        $expectedHash = 'sha384-abc123';

        $lookup = \Mockery::mock(EntrypointsLookup::class);
        $lookup->shouldReceive('getJSFiles')
            ->once()
            ->with($entrypoint)
            ->andReturn($fileData);
        $lookup->shouldReceive('getFileHash')
            ->once()
            ->with($fileData[0])
            ->andReturn($expectedHash);

        $collection = \Mockery::mock(EntrypointsLookupCollection::class);
        $collection->shouldReceive('getEntrypointsLookup')
            ->once()
            ->andReturn($lookup);

        $query = new EntrypointIntegrityHashViteQuery($collection);
        $result = $query($entrypoint);

        $this->assertEquals($expectedHash, $result);
    }

    public function testInvokeReturnsNullWhenNoFiles(): void
    {
        $entrypoint = 'nonexistent';

        $lookup = \Mockery::mock(EntrypointsLookup::class);
        $lookup->shouldReceive('getJSFiles')
            ->once()
            ->with($entrypoint)
            ->andReturn([]);

        $collection = \Mockery::mock(EntrypointsLookupCollection::class);
        $collection->shouldReceive('getEntrypointsLookup')
            ->once()
            ->andReturn($lookup);

        $query = new EntrypointIntegrityHashViteQuery($collection);
        $result = $query($entrypoint);

        $this->assertNull($result);
    }

    public function testInvokeUsesFirstFileFromArray(): void
    {
        $entrypoint = 'app';
        $fileData = ['app.js', 'vendor.js', 'styles.css'];
        $expectedHash = 'sha384-xyz789';

        $lookup = \Mockery::mock(EntrypointsLookup::class);
        $lookup->shouldReceive('getJSFiles')
            ->once()
            ->with($entrypoint)
            ->andReturn($fileData);
        $lookup->shouldReceive('getFileHash')
            ->once()
            ->with($fileData[0])
            ->andReturn($expectedHash);

        $collection = \Mockery::mock(EntrypointsLookupCollection::class);
        $collection->shouldReceive('getEntrypointsLookup')
            ->once()
            ->andReturn($lookup);

        $query = new EntrypointIntegrityHashViteQuery($collection);
        $result = $query($entrypoint);

        $this->assertEquals($expectedHash, $result);
    }
}
