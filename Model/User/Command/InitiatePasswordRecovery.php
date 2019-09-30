<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Command;

use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Messaging\Command;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\UserId;

final class InitiatePasswordRecovery extends Command
{
    public static function now(UserId $userId, Email $email): self
    {
        return new self([
            'userId' => $userId->toString(),
            'email'  => $email->toString(),
        ]);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload()['userId']);
    }

    public function email(): Email
    {
        return Email::fromString($this->payload()['email']);
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        Assert::keyExists($payload, 'email');

        parent::setPayload($payload);
    }
}
