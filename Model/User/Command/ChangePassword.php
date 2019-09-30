<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Command;

use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Messaging\Command;
use Xm\SymfonyBundle\Model\User\UserId;

final class ChangePassword extends Command
{
    public static function forUser(
        UserId $userId,
        string $encodedPassword
    ): self {
        return new self([
            'userId'          => $userId->toString(),
            'encodedPassword' => $encodedPassword,
        ]);
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload()['userId']);
    }

    public function encodedPassword(): string
    {
        return $this->payload()['encodedPassword'];
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        Assert::keyExists($payload, 'encodedPassword');
        Assert::notEmpty($payload['encodedPassword']);
        Assert::string($payload['encodedPassword']);

        parent::setPayload($payload);
    }
}
