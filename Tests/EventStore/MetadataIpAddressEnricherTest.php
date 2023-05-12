<?php

declare(strict_types=1);

namespace EventStore;

use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\EventStore\MetadataIpAddressEnricher;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class MetadataIpAddressEnricherTest extends BaseTestCase
{
    public function testIpv4(): void
    {
        $faker = $this->faker();
        $ipAddress = $faker->ipv4();

        $requestInfoProvider = \Mockery::mock(RequestInfoProvider::class);
        $requestInfoProvider->shouldReceive('ipAddress')
            ->once()
            ->andReturn($ipAddress);

        $enricher = new MetadataIpAddressEnricher($requestInfoProvider);

        $event = $enricher->enrich(AggregateChanged::occur($faker->uuid(), []));

        $this->assertArrayHasKey('ipAddress', $event->metadata());
        $this->assertEquals($ipAddress, $event->metadata()['ipAddress']);
    }

    public function testIpv6(): void
    {
        $faker = $this->faker();
        $ipAddress = $faker->ipv6();

        $requestInfoProvider = \Mockery::mock(RequestInfoProvider::class);
        $requestInfoProvider->shouldReceive('ipAddress')
            ->once()
            ->andReturn($ipAddress);

        $enricher = new MetadataIpAddressEnricher($requestInfoProvider);

        $event = $enricher->enrich(AggregateChanged::occur($faker->uuid(), []));

        $this->assertArrayHasKey('ipAddress', $event->metadata());
        $this->assertEquals($ipAddress, $event->metadata()['ipAddress']);
    }

    public function testCli(): void
    {
        $faker = $this->faker();

        $requestInfoProvider = \Mockery::mock(RequestInfoProvider::class);
        $requestInfoProvider->shouldReceive('ipAddress')
            ->once()
            ->andReturnNull();

        $enricher = new MetadataIpAddressEnricher($requestInfoProvider);

        $event = $enricher->enrich(AggregateChanged::occur($faker->uuid(), []));

        $this->assertArrayNotHasKey('ipAddress', $event->metadata());
    }
}
