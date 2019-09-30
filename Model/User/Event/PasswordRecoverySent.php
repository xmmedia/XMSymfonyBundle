<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Event;

use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\EmailGatewayMessageId;
use Xm\SymfonyBundle\Model\NotificationGatewayId;
use Xm\SymfonyBundle\Model\User\Token;
use Xm\SymfonyBundle\Model\User\UserId;

class PasswordRecoverySent extends AggregateChanged
{
    /** @var Token */
    private $token;

    /** @var NotificationGatewayId */
    private $messageId;

    public static function now(
        UserId $userId,
        Token $token,
        NotificationGatewayId $messageId
    ): self {
        $event = self::occur($userId->toString(), [
            'token'     => $token->toString(),
            'messageId' => $messageId->toString(),
        ]);

        $event->token = $token;
        $event->messageId = $messageId;

        return $event;
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->aggregateId());
    }

    public function token(): Token
    {
        if (null === $this->token) {
            $this->token = Token::fromString($this->payload()['token']);
        }

        return $this->token;
    }

    public function messageId(): NotificationGatewayId
    {
        if (null === $this->messageId) {
            $this->messageId = EmailGatewayMessageId::fromString(
                $this->payload()['messageId']
            );
        }

        return $this->messageId;
    }
}
