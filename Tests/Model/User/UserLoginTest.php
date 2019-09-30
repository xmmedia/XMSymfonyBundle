<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User;

use Xm\SymfonyBundle\Model\User\Event;
use Xm\SymfonyBundle\Model\User\Exception;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UserLoginTest extends BaseTestCase
{
    use UserTestTrait;

    public function testLoggedIn(): void
    {
        $user = $this->getUserActive();

        $user->loggedIn();

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\UserLoggedIn::class,
            [],
            $events
        );

        $this->assertCount(1, $events);
    }

    public function testLoggedInUnverified(): void
    {
        $user = $this->getUserActive(true);

        $this->expectException(Exception\UserNotVerified::class);

        $user->loggedIn();
    }

    public function testLoggedInInactive(): void
    {
        $user = $this->getUserInactive();

        $this->expectException(Exception\InvalidUserActiveStatus::class);

        $user->loggedIn();
    }
}
