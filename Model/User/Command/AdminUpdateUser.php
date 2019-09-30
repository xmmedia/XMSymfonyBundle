<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Command;

use Webmozart\Assert\Assert;
use Xm\SymfonyBundle\Messaging\Command;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\Name;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Model\User\UserId;

final class AdminUpdateUser extends Command
{
    public static function with(
        UserId $userId,
        Email $email,
        Role $role,
        Name $firstName,
        Name $lastName
    ): self {
        return new self([
            'userId'          => $userId->toString(),
            'email'           => $email->toString(),
            'role'            => $role->getValue(),
            'firstName'       => $firstName->toString(),
            'lastName'        => $lastName->toString(),
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

    public function role(): Role
    {
        return Role::byValue($this->payload()['role']);
    }

    public function firstName(): Name
    {
        return Name::fromString($this->payload()['firstName']);
    }

    public function lastName(): Name
    {
        return Name::fromString($this->payload()['lastName']);
    }

    protected function setPayload(array $payload): void
    {
        Assert::keyExists($payload, 'userId');
        Assert::uuid($payload['userId']);

        Assert::keyExists($payload, 'email');

        Assert::keyExists($payload, 'role');

        Assert::keyExists($payload, 'firstName');

        Assert::keyExists($payload, 'lastName');

        parent::setPayload($payload);
    }
}
