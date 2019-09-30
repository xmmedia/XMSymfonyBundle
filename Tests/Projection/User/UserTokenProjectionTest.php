<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Projection\User;

use Mockery;
use Prooph\EventStore\Projection\ReadModelProjector;
use Xm\SymfonyBundle\Model\User\Event;
use Xm\SymfonyBundle\Projection\User\UserTokenProjection;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UserTokenProjectionTest extends BaseTestCase
{
    public function test(): void
    {
        $projectedEvents = [
            Event\InviteSent::class,
            Event\PasswordRecoverySent::class,
            Event\UserVerified::class,
            Event\ChangedPassword::class,
        ];

        $projection = new UserTokenProjection();

        $projector = Mockery::mock(ReadModelProjector::class);
        $projector->shouldReceive('fromStream')
            ->once()
            ->with('user')
            ->andReturnSelf();

        $projector->shouldReceive('when')
            ->withArgs(function ($eventHandlers) use ($projectedEvents) {
                if (!\is_array($eventHandlers)) {
                    return false;
                }

                foreach ($projectedEvents as $event) {
                    if (!\array_key_exists($event, $eventHandlers)) {
                        return false;
                    }
                }

                return true;
            });

        $projection->project($projector);
    }
}
