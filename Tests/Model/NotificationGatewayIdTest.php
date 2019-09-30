<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use Xm\SymfonyBundle\Model\NotificationGatewayId;
use Xm\SymfonyBundle\Tests\BaseTestCase;
use Xm\SymfonyBundle\Tests\FakeVo;

class NotificationGatewayIdTest extends BaseTestCase
{
    public function testFromString(): void
    {
        $id = NotificationGatewayId::fromString('string');

        $this->assertEquals('string', $id->toString());
        $this->assertEquals('string', (string) $id);
    }

    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        NotificationGatewayId::fromString('');
    }

    public function testSameValueAs(): void
    {
        $id1 = NotificationGatewayId::fromString('string');
        $id2 = NotificationGatewayId::fromString('string');

        $this->assertTrue($id1->sameValueAs($id2));
    }

    public function testSameValueAsDiffClass(): void
    {
        $id = NotificationGatewayId::fromString('string');

        $this->assertFalse($id->sameValueAs(FakeVo::create()));
    }
}
