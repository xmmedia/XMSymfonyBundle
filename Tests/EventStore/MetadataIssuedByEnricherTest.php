<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\EventStore;

use Mockery;
use Xm\SymfonyBundle\DataProvider\IssuerProvider;
use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\EventStore\MetadataIssuedByEnricher;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class MetadataIssuedByEnricherTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();
        $uuid = $faker->uuid;

        $issuerProvider = Mockery::mock(IssuerProvider::class);
        $issuerProvider->shouldReceive('getIssuer')
            ->once()
            ->andReturn($uuid);

        $enricher = new MetadataIssuedByEnricher($issuerProvider);

        $event = $enricher->enrich(AggregateChanged::occur($faker->uuid, []));

        $this->assertArrayHasKey('issuedBy', $event->metadata());
        $this->assertEquals($uuid, $event->metadata()['issuedBy']);
    }
}
