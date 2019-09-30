<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use Xm\SymfonyBundle\Model\UuidId;
use Xm\SymfonyBundle\Model\UuidIdGeneratable;
use Xm\SymfonyBundle\Model\ValueObject;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class UuidIdGeneratableTest extends BaseTestCase
{
    public function testGenerate(): void
    {
        $uuid = UuidIdGeneratableId::generate();

        $this->assertInstanceOf(UuidIdGeneratableId::class, $uuid);
        $this->assertUuid($uuid->toString());
    }
}

class UuidIdGeneratableId implements ValueObject
{
    use UuidId;
    use UuidIdGeneratable;
}
