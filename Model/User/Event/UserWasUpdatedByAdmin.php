<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Model\User\Event;

use Xm\SymfonyBundle\EventSourcing\AggregateChanged;
use Xm\SymfonyBundle\Model\Email;
use Xm\SymfonyBundle\Model\User\Name;
use Xm\SymfonyBundle\Model\User\Role;
use Xm\SymfonyBundle\Model\User\UserId;

class UserWasUpdatedByAdmin extends AggregateChanged
{
    /** @var Email */
    private $email;

    /** @var Role */
    private $role;

    /** @var Name */
    private $firstName;

    /** @var Name */
    private $lastName;

    public static function now(
        UserId $userId,
        Email $email,
        Role $role,
        Name $firstName,
        Name $lastName
    ): self {
        $event = self::occur($userId->toString(), [
            'email'     => $email->toString(),
            'role'      => $role->getValue(),
            'firstName' => $firstName->toString(),
            'lastName'  => $lastName->toString(),
        ]);

        $event->email = $email;
        $event->role = $role;
        $event->firstName = $firstName;
        $event->lastName = $lastName;

        return $event;
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->aggregateId());
    }

    public function email(): Email
    {
        if (null === $this->email) {
            $this->email = Email::fromString($this->payload()['email']);
        }

        return $this->email;
    }

    public function role(): Role
    {
        if (null === $this->role) {
            $this->role = Role::byValue($this->payload()['role']);
        }

        return $this->role;
    }

    public function firstName(): Name
    {
        if (null === $this->firstName) {
            $this->firstName = Name::fromString($this->payload()['firstName']);
        }

        return $this->firstName;
    }

    public function lastName(): Name
    {
        if (null === $this->lastName) {
            $this->lastName = Name::fromString($this->payload()['lastName']);
        }

        return $this->lastName;
    }
}
