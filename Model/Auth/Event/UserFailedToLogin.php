<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\Auth\Event;

use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\Auth\AuthId;

class UserFailedToLogin extends AggregateChanged
{
    /** @var string */
    private $email;

    /** @var string */
    private $userAgent;

    /** @var string */
    private $ipAddress;

    /** @var string */
    private $exceptionMessage;

    public static function now(
        AuthId $authId,
        ?string $email,
        ?string $userAgent,
        string $ipAddress,
        ?string $exceptionMessage
    ): self {
        $event = self::occur($authId->toString(), [
            'email'            => $email,
            'userAgent'        => $userAgent,
            'ipAddress'        => $ipAddress,
            'exceptionMessage' => $exceptionMessage,
        ]);

        $event->email = $email;
        $event->userAgent = $userAgent;
        $event->ipAddress = $ipAddress;
        $event->exceptionMessage = $exceptionMessage;

        return $event;
    }

    public function authId(): AuthId
    {
        return AuthId::fromString($this->aggregateId());
    }

    public function email(): ?string
    {
        if (null === $this->email) {
            $this->email = $this->payload()['email'];
        }

        return $this->email;
    }

    public function userAgent(): ?string
    {
        if (null === $this->userAgent) {
            $this->userAgent = $this->payload()['userAgent'];
        }

        return $this->userAgent;
    }

    public function ipAddress(): string
    {
        if (null === $this->ipAddress) {
            $this->ipAddress = $this->payload()['ipAddress'];
        }

        return $this->ipAddress;
    }

    public function exceptionMessage(): ?string
    {
        if (null === $this->exceptionMessage) {
            $this->exceptionMessage = $this->payload()['exceptionMessage'];
        }

        return $this->exceptionMessage;
    }
}
