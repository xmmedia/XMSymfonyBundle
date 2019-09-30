<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Command;

use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Messaging\Command;
use Xm\SymfonyBundle\Model\User\UserId;

final class VerifyUserByAdmin extends Command
{
    public static function now(UserId $userId): self
    {
        return new self([
            'userId' => $userId->toString(),
        ]);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload()['userId']);
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        parent::setPayload($payload);
    }
}
