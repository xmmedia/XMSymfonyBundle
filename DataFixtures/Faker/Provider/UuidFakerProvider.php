<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataFixtures\Faker\Provider;

use Faker;
use Xm\SymfonyBundle\Model\Auth\AuthId;
use Xm\SymfonyBundle\Model\User\UserId;
use Xm\SymfonyBundle\Tests\FakeId;

/**
 * @property FakeId $fakeId
 *
 * @codeCoverageIgnore
 */
class UuidFakerProvider extends Faker\Provider\Uuid
{
    public function fakeId(): FakeId
    {
        return FakeId::fromString(parent::uuid());
    }

    public function authId(): AuthId
    {
        return AuthId::fromString(parent::uuid());
    }

    public function userId(): UserId
    {
        return UserId::fromString(parent::uuid());
    }
}
