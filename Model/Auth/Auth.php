<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\Auth;

use Xm\SymfonyBundle\EventSourcing\Aggregate\AggregateRoot;
use Xm\SymfonyBundle\EventSourcing\AppliesAggregateChanged;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\Entity;
use Xm\SymfonyBundle\Model\User\UserId;

class Auth extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    /** @var AuthId */
    private $authId;

    public static function success(
        AuthId $authId,
        UserId $userId,
        Email $email,
        string $userAgent,
        string $ipAddress
    ): self {
        $self = new self();
        $self->recordThat(
            Event\UserLoggedIn::now(
                $authId,
                $userId,
                $email,
                $userAgent,
                $ipAddress
            )
        );

        return $self;
    }

    public static function failure(
        AuthId $authId,
        ?string $email,
        ?string $userAgent,
        string $ipAddress,
        ?string $exceptionMessage
    ): self {
        $self = new self();
        $self->recordThat(
            Event\UserFailedToLogin::now(
                $authId,
                $email,
                $userAgent,
                $ipAddress,
                $exceptionMessage
            )
        );

        return $self;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function aggregateId(): string
    {
        return $this->authId->toString();
    }

    public function authId(): AuthId
    {
        return $this->authId;
    }

    protected function whenUserLoggedIn(Event\UserLoggedIn $event): void
    {
        $this->authId = $event->authId();
    }

    protected function whenUserFailedToLogin(Event\UserFailedToLogin $event): void
    {
        $this->authId = $event->authId();
    }

    /**
     * @param Auth|Entity $other
     */
    public function sameIdentityAs(Entity $other): bool
    {
        if (\get_class($this) !== \get_class($other)) {
            return false;
        }

        return $this->authId->sameValueAs($other->authId);
    }
}
