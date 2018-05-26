<?php
namespace Tests;

use App\BasketItem;
use App\BasketItemCollection;
use App\Widget;
use PHPUnit\Framework\TestCase;

class BasketItemCollectionTest extends TestCase
{
    /**
     * @test
     * @dataProvider validBasketItemDataProvider
     * @param BasketItem ...$items
     */
    public function collectBasketItems(BasketItem ...$items)
    {
        $collection = collectInto(BasketItemCollection::class, $items);
        self::assertCount(count($items), $collection);
    }

    /**
     * @test
     * @dataProvider validBasketItemDataProvider
     * @param BasketItem ...$items
     */
    public function appendBasketItems(BasketItem ...$items)
    {
        $collection = new BasketItemCollection();
        $count = 0;
        foreach ($items as $item) {
            $collection[] = $item;
            $count++;
            $this->assertCount($count, $collection);
        }
    }

    /**
     * @test
     * @dataProvider invalidBasketItemDataProvider
     * @expectedException \TypeError
     * @param $invalidThing
     */
    public function offsetSetInvalidWidget($invalidThing)
    {
        $collection = new BasketItemCollection();
        $collection[] = $invalidThing;
    }

    /**
     * @test
     * @dataProvider invalidBasketItemDataProvider
     * @expectedException \TypeError
     * @param $invalidThing
     */
    public function appendingInvalidWidget($invalidThing)
    {
        $collection = new BasketItemCollection();
        $collection->append($invalidThing);
    }

    /**
     * @test
     * @expectedException \TypeError
     */
    public function collectingInvalidBasketItems()
    {
        new BasketItemCollection([], (object)[], 123);
    }

    public function validBasketItemDataProvider(): array
    {
        $w1 = $this->createMock(Widget::class);
        $w1->method('code')->willReturn('w1');
        $w2 = $this->createMock(Widget::class);
        $w2->method('code')->willReturn('w2');
        return [
            [new BasketItem($w1, 1)],
            [new BasketItem($w1, 5), new BasketItem($w2)],
        ];
    }

    public function invalidBasketItemDataProvider(): array
    {
        return [
            [[]],
            [(object)[]],
            [1, 2, 3],
            [new BasketItemCollection],
        ];
    }
}