<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Messenger;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Xm\SymfonyBundle\DataProvider\IssuerProvider;
use Xm\SymfonyBundle\Messenger\CommandEnricherMiddleware;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeEvent;

class CommandEnricherMiddlewareTest extends BaseTestCase
{
    public function test(): void
    {
        $faker = $this->faker();
        $uuid = $faker->uuid();

        $issuerProvider = \Mockery::mock(IssuerProvider::class);
        $issuerProvider->shouldReceive('getIssuer')
            ->once()
            ->andReturn($uuid);

        $middleware = new CommandEnricherMiddleware($issuerProvider);

        $envelope = $middleware->handle(
            new Envelope(FakeEvent::performed()),
            new StackMiddleware(),
        );

        $this->assertArrayHasKey(
            'issuedBy',
            $envelope->getMessage()->metadata(),
        );
        $this->assertEquals(
            $uuid,
            $envelope->getMessage()->metadata()['issuedBy'],
        );
    }
}
