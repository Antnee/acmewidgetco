<?php
namespace Tests;

use App\BasketItem;
use App\Widget;
use Money\Money;
use PHPUnit\Framework\TestCase;

class BasketItemTest extends TestCase
{
    private $widget;

    public function setUp()
    {
        parent::setUp();
        $this->widget = $this->createMock(Widget::class);
        $this->widget->method('code')->willReturn('CODE');
        $this->widget->method('price')->willReturn(Money::USD(1234));
    }

    /**
     * @test
     */
    public function basketItem()
    {
        $basketItem = new BasketItem($this->widget, 1);
        $this->assertInstanceOf(BasketItem::class, $basketItem);
        $this->assertEquals(1, $basketItem->qty());

        $newBasketItem = $basketItem->withQty(5);
        $this->assertEquals(5, $newBasketItem->qty());
        $this->assertNotEquals($newBasketItem, $basketItem);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function negativeQtyInConstructor()
    {
        new BasketItem($this->widget, -1);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function negativeQtyInUpdate()
    {
        $basketItem = new BasketItem($this->widget, 1);
        $basketItem->withQty(-1);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function zeroQtyInConstructor()
    {
        new BasketItem($this->widget, 0);
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function zeroQtyInUpdate()
    {
        $basketItem = new BasketItem($this->widget, 1);
        $basketItem->withQty(0);
    }

    /**
     * @test
     */
    public function getCode()
    {
        $basketItem = new BasketItem($this->widget, 1);
        $this->assertEquals('CODE', $basketItem->code());
    }

    /**
     * @test
     * @dataProvider qtyProvider
     * @param int $qty
     * @throws \Exception
     */
    public function getPrice(int $qty)
    {
        $basketItem = new BasketItem($this->widget, $qty);
        $this->assertTrue(Money::USD($qty*1234)->equals($basketItem->price()));
    }

    public function qtyProvider(): array
    {
        return [
            [1], [2], [10], [50]
        ];
    }
}