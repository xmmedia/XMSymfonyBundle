<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\DataFixtures\Faker\Provider;

use Faker;
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
}
