<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Type;

use GraphQL\Error\Error;
use PHPUnit\Framework\TestCase;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Type\ProvinceInputType;
use Xm\SymfonyBundle\Model\Province;

class ProvinceInputTypeTest extends TestCase
{
    public function test(): void
    {
        $type = new ProvinceInputType();

        $this->assertCount(64, $type->getValues());
        $this->assertNotNull($type->description);
    }

    public function testSerialize(): void
    {
        $type = new ProvinceInputType();

        $result = $type->serialize(Province::fromString('AB'));

        $this->assertEquals('AB', $result);
    }

    public function testSerializeNotProvince(): void
    {
        $type = new ProvinceInputType();

        $this->expectException(Error::class);

        $type->serialize('string');
    }

    public function testGetAliases(): void
    {
        $this->assertEquals(['ProvinceInput'], ProvinceInputType::getAliases());
    }
}
