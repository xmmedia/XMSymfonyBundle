<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User;

use Xm\SymfonyBundle\Model\EmailGatewayMessageId;
use Xm\SymfonyBundle\Model\User\Event;
use Xm\SymfonyBundle\Model\User\Exception;
use Xm\SymfonyBundle\Model\User\Token;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UserInviteTest extends BaseTestCase
{
    use UserTestTrait;

    public function testInviteSent(): void
    {
        $faker = $this->faker();

        $user = $this->getUserActive(true);

        $token = Token::fromString($faker->asciify(str_repeat('*', 25)));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $user->inviteSent($token, $messageId);

        $events = $this->popRecordedEvent($user);

        $this->assertRecordedEvent(
            Event\InviteSent::class,
            [
                'token'     => $token->toString(),
                'messageId' => $messageId->toString(),
            ],
            $events
        );

        $this->assertCount(1, $events);
    }

    public function testInviteSentAlreadyVerified(): void
    {
        $faker = $this->faker();
        $token = Token::fromString($faker->asciify(str_repeat('*', 25)));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $user = $this->getUserActive();

        $this->expectException(Exception\UserAlreadyVerified::class);

        $user->inviteSent($token, $messageId);
    }
}
