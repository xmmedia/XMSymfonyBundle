<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\EventStore;

use Prooph\Common\Messaging\Message;
use Xm\SymfonyBundle\DataProvider\CausationProvider;
use Xm\SymfonyBundle\EventStore\MetadataCausationEnricher;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class MetadataCausationEnricherTest extends BaseTestCase
{
    public function testEnrichWithCausationId(): void
    {
        $causationId = \Ramsey\Uuid\Uuid::uuid4();

        $causationProvider = \Mockery::mock(CausationProvider::class);
        $causationProvider->shouldReceive('retrieveCausationId')
            ->andReturn($causationId);

        $enrichedMessage = \Mockery::mock(Message::class);

        $message = \Mockery::mock(Message::class);
        $message->shouldReceive('withAddedMetadata')
            ->once()
            ->with('causationId', $causationId->toString())
            ->andReturn($enrichedMessage);

        $enricher = new MetadataCausationEnricher($causationProvider);
        $result = $enricher->enrich($message);

        $this->assertSame($enrichedMessage, $result);
    }

    public function testEnrichWithNullCausationId(): void
    {
        $causationProvider = \Mockery::mock(CausationProvider::class);
        $causationProvider->shouldReceive('retrieveCausationId')
            ->once()
            ->andReturn(null);

        $message = \Mockery::mock(Message::class);
        $message->shouldNotReceive('withAddedMetadata');

        $enricher = new MetadataCausationEnricher($causationProvider);
        $result = $enricher->enrich($message);

        $this->assertSame($message, $result);
    }
}
