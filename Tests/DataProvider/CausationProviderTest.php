<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\DataProvider;

use Ramsey\Uuid\Uuid;
use Symfony\Contracts\Service\ResetInterface;
use Xm\SymfonyBundle\DataProvider\CausationProvider;
use Xm\SymfonyBundle\Tests\BaseTestCase;

class CausationProviderTest extends BaseTestCase
{
    public function testNoCausationId(): void
    {
        $this->assertNull((new CausationProvider())->retrieveCausationId());
    }

    public function testStoreCausationId(): void
    {
        $provider = new CausationProvider();
        $uuid = Uuid::uuid4();

        $provider->storeCausationId($uuid);

        $this->assertSame($uuid, $provider->retrieveCausationId());
    }

    public function testStoreReplacesCausationId(): void
    {
        $provider = new CausationProvider();
        $uuid = Uuid::uuid4();

        $provider->storeCausationId(Uuid::uuid4());
        $provider->storeCausationId($uuid);

        $this->assertSame($uuid, $provider->retrieveCausationId());
    }

    public function testReset(): void
    {
        $provider = new CausationProvider();
        $provider->storeCausationId(Uuid::uuid4());

        $provider->reset();

        $this->assertInstanceOf(ResetInterface::class, $provider);
        $this->assertNull($provider->retrieveCausationId());
    }
}
