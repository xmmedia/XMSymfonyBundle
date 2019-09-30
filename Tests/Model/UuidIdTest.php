<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use Xm\SymfonyBundle\Model\UuidId;
use Xm\SymfonyBundle\Model\ValueObject;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeVo;

class UuidIdTest extends BaseTestCase
{
    public function testFromString(): void
    {
        $faker = $this->faker();

        $uuidString = $faker->uuid;

        $uuid = UuidIdId::fromString($uuidString);

        $this->assertEquals($uuidString, $uuid->toString());
        $this->assertEquals($uuidString, (string) $uuid);

        $this->assertEquals(
            \Ramsey\Uuid\Uuid::fromString($uuidString),
            $uuid->uuid()
        );
    }

    public function testFromUuid(): void
    {
        $faker = $this->faker();

        $uuidString = $faker->uuid;

        $uuid = UuidIdId::fromUuid(\Ramsey\Uuid\Uuid::fromString($uuidString));

        $this->assertEquals($uuidString, $uuid->toString());
        $this->assertEquals($uuidString, (string) $uuid);

        $this->assertEquals(
            \Ramsey\Uuid\Uuid::fromString($uuidString),
            $uuid->uuid()
        );
    }

    public function testSameValueAs(): void
    {
        $faker = $this->faker();

        $uuidString = $faker->uuid;

        $uuid1 = UuidIdId::fromString($uuidString);
        $uuid2 = UuidIdId::fromString($uuidString);

        $this->assertTrue($uuid1->sameValueAs($uuid2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $faker = $this->faker();

        $uuidString = $faker->uuid;

        $uuid = UuidIdId::fromString($uuidString);

        $this->assertFalse($uuid->sameValueAs(FakeVo::create()));
    }
}

class UuidIdId implements ValueObject
{
    use UuidId;
}
