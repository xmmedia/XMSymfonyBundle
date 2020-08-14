<?php

declare(strict_types=1);

namespace Xm\SymfonyBundle\Tests\Model;

use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testSameValuesAs(): void
    {
        $arr1 = [1, 2, 3, 4, 5];
        $arr2 = [1, 2, 3, 4, 5];

        $collection1 = Collection::fromArray($arr1);
        $collection2 = Collection::fromArray($arr2);

        $this->assertTrue($collection1->sameValuesAs($collection2));
    }

    public function testSameValuesAsDiffClass(): void
    {
        $arr1 = [1, 2, 3, 4, 5];
        $arr2 = [1, 2, 3, 4, 5];

        $collection1 = Collection::fromArray($arr1);
        $collection2 = CollectionOther::fromArray($arr2);

        $this->assertFalse($collection1->sameValuesAs($collection2));
    }

    public function testSameValuesAsDiffItemCount(): void
    {
        $arr1 = [1, 2];
        $arr2 = [1, 2, 3, 4, 5];

        $collection1 = Collection::fromArray($arr1);
        $collection2 = Collection::fromArray($arr2);

        $this->assertFalse($collection1->sameValuesAs($collection2));
    }

    public function testFind(): void
    {
        $arr = [1, 2, 3];

        $collection = Collection::fromArray($arr);

        $res = $collection->find(function ($i) {
            return 1 === $i;
        });

        $this->assertEquals(1, $res);
    }

    public function testFindNone(): void
    {
        $arr = [1];

        $collection = Collection::fromArray($arr);

        $res = $collection->find(function ($i) {
            return 0 === $i;
        });

        $this->assertNull($res);
    }

    public function testJsonSerialize(): void
    {
        $str = json_encode([1]);

        $collection = Collection::fromArray([1]);

        $this->assertEquals($str, json_encode($collection));
    }
}

class Collection extends \Xm\SymfonyBundle\Model\Collection
{
}
class CollectionOther extends \Xm\SymfonyBundle\Model\Collection
{
}
