<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model\User\Event;

use Xm\SymfonyBundle\Model\EmailGatewayMessageId;
use Xm\SymfonyBundle\Model\User\Event\InviteSent;
use Xm\SymfonyBundle\Model\User\Token;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\CanCreateEventFromArray;

class InviteSentTest extends BaseTestCase
{
    use CanCreateEventFromArray;

    public function testOccur(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $token = Token::fromString($faker->asciify('token'));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        $event = InviteSent::now($userId, $token, $messageId);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($token, $event->token());
        $this->assertEquals($messageId, $event->messageId());
    }

    public function testFromArray(): void
    {
        $faker = $this->faker();

        $userId = $faker->userId;
        $token = Token::fromString($faker->asciify('token'));
        $messageId = EmailGatewayMessageId::fromString($faker->uuid);

        /** @var InviteSent $event */
        $event = $this->createEventFromArray(
            InviteSent::class,
            $userId->toString(),
            [
                'token'     => $token->toString(),
                'messageId' => $messageId->toString(),
            ]
        );

        $this->assertInstanceOf(InviteSent::class, $event);

        $this->assertEquals($userId, $event->userId());
        $this->assertEquals($token, $event->token());
        $this->assertEquals($messageId, $event->messageId());
    }
}
