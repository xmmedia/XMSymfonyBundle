<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Infrastructure\GraphQl\Type;

use GraphQL\Error\Error;
use PHPUnit\Framework\TestCase;
use Xm\SymfonyBundle\Infrastructure\GraphQl\Type\GenderType;
use Xm\SymfonyBundle\Model\Gender;

class GenderTypeTest extends TestCase
{
    public function test(): void
    {
        $type = new GenderType();

        $this->assertCount(2, $type->getValues());
        $this->assertNotNull($type->description);
    }

    public function testSerialize(): void
    {
        $type = new GenderType();

        $result = $type->serialize(Gender::byValue('MALE'));

        $this->assertEquals('MALE', $result);
    }

    public function testSerializeNotGender(): void
    {
        $type = new GenderType();

        $this->expectException(Error::class);

        $type->serialize('string');
    }

    public function testGetAliases(): void
    {
        $this->assertEquals(['Gender'], GenderType::getAliases());
    }
}
