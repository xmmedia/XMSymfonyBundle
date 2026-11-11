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

    public function testInvokeWithNullCollection(): void
    {
        $query = new EntrypointIntegrityHashQuery(null);

        $this->assertNull($query('app'));
    }

    public function testInvokeWithoutCollection(): void
    {
        $query = new EntrypointIntegrityHashQuery();

        $this->assertNull($query('app'));
    }

    public function testInvokeReturnsNullWhenNoJavaScriptFiles(): void
    {
        $lookup = \Mockery::mock(EntrypointLookupInterface::class);
        $lookup->shouldReceive('getJavaScriptFiles')
            ->once()
            ->with('app')
            ->andReturn([]);
        $lookup->shouldNotReceive('getIntegrityData');

        $this->assertNull($this->query($lookup)('app'));
    }

    public function testInvokeReturnsNullWhenFileNotInIntegrityData(): void
    {
        $lookup = \Mockery::mock(EntrypointLookupInterface::class);
        $lookup->shouldReceive('getJavaScriptFiles')
            ->once()
            ->with('app')
            ->andReturn(['/build/app.js']);
        $lookup->shouldReceive('getIntegrityData')
            ->once()
            ->andReturn(['/build/other.js' => 'sha384-other']);

        $this->assertNull($this->query($lookup)('app'));
    }

    private function query(EntrypointLookupInterface $lookup): EntrypointIntegrityHashQuery
    {
        $collection = \Mockery::mock(EntrypointLookupCollectionInterface::class);
        $collection->shouldReceive('getEntrypointLookup')
            ->once()
            ->andReturn($lookup);

        return new EntrypointIntegrityHashQuery($collection);
    }
}
