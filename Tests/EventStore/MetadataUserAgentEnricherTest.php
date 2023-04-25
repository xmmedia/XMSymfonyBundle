<?php

declare(strict_types=1);

namespace EventStore;

use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\EventStore\MetadataIpAddressEnricher;
use Xm\SymfonyBundle\EventStore\MetadataUserAgentEnricher;
use Xm\SymfonyBundle\Infrastructure\Service\RequestInfoProvider;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class MetadataUserAgentEnricherTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();
        $userAgent = $faker->userAgent();

        $requestInfoProvider = \Mockery::mock(RequestInfoProvider::class);
        $requestInfoProvider->shouldReceive('userAgent')
            ->once()
            ->andReturn($userAgent);

        $enricher = new MetadataUserAgentEnricher($requestInfoProvider);

        $event = $enricher->enrich(AggregateChanged::occur($faker->uuid(), []));

        $this->assertArrayHasKey('userAgent', $event->metadata());
        $this->assertEquals($userAgent, $event->metadata()['userAgent']);
    }
}
