<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\Auth\Command;

use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Messaging\Command;
use Xm\SymfonyBundle\Model\Auth\AuthId;

final class UserLoginFailed extends Command
{
    public static function now(
        AuthId $authId,
        ?string $email,
        ?string $userAgent,
        string $ipAddress,
        ?string $exceptionMessage
    ): self {
        return new self([
            'authId'           => $authId->toString(),
            'email'            => $email,
            'userAgent'        => $userAgent,
            'ipAddress'        => $ipAddress,
            'exceptionMessage' => $exceptionMessage,
        ]);
    }

    public function authId(): AuthId
    {
        return AuthId::fromString($this->payload()['authId']);
    }

    public function email(): ?string
    {
        return $this->payload()['email'];
    }

    public function userAgent(): ?string
    {
        return $this->payload()['userAgent'];
    }

    public function ipAddress(): string
    {
        return $this->payload()['ipAddress'];
    }

    public function exceptionMessage(): ?string
    {
        return $this->payload()['exceptionMessage'];
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'authId');
        Assert::uuid($payload['authId']);

        Assert::keyExists($payload, 'email');

        Assert::keyExists($payload, 'userAgent');

        Assert::keyExists($payload, 'ipAddress');
        Assert::notEmpty($payload['ipAddress']);
        Assert::string($payload['ipAddress']);

        Assert::keyExists($payload, 'exceptionMessage');

        parent::setPayload($payload);
    }
}
