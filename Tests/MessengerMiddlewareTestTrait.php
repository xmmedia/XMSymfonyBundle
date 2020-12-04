<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Mockery;

trait MessengerMiddlewareTestTrait
{
    private function getStackMock(): StackInterface
    {
        $nextMiddleware = Mockery::mock(MiddlewareInterface::class);
        $nextMiddleware->shouldReceive('handle')
            ->once()
            ->andReturnUsing(
                function (Envelope $envelope, StackInterface $stack): Envelope {
                    return $envelope;
                }
            );

        return new StackMiddleware($nextMiddleware);
    }
}
