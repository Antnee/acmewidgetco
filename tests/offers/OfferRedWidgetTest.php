<?php
namespace Tests;

use App\Basket;
use App\BasketItem;
use App\Offer;
use App\OfferNull;
use App\OfferRedWidgetBulk;
use Money\Money;
use PHPUnit\Framework\TestCase;


class OfferRedWidgetTest extends TestCase
{
    /** @var OfferRedWidgetBulk */
    private $offer;

    public function setUp()
    {
        parent::setUp();
        $this->offer = new OfferRedWidgetBulk;
    }

    /**
     * @test
     */
    public function checkReturnTypes()
    {
        $basket = $this->createMock(Basket::class);
        $basket->method('getItemFromBasketBySku')->willThrowException(new \Exception);
        $this->assertFalse($this->offer->applicable($basket));
        $this->assertInternalType('string', $this->offer->details());
        $this->assertInstanceOf(Offer::class, $this->offer->next(new OfferNull));
    }

    /**
     * @test
     */
    public function doesNotApplyForOne()
    {
        $item = $this->createMock(BasketItem::class);
        $item->method('price')->willReturn(Money::USD(3295));
        $item->method('qty')->willReturn(1);

        $basket = $this->createMock(Basket::class);
        $basket->method('getItemFromBasketBySku')->willReturn($item);

        $this->assertTrue($this->offer->apply($basket)->equals(Money::USD(0)));
        $this->offer->next(new OfferRedWidgetBulk);
        $this->assertTrue($this->offer->apply($basket)->equals(Money::USD(0)));
        $this->offer->next(new OfferRedWidgetBulk);
        $this->assertTrue($this->offer->apply($basket)->equals(Money::USD(0)));
    }

    /**
     * @test
     * @dataProvider discountValues
     * @param int $qty
     * @param int $discountCents
     * @throws \Exception
     */
    public function appliesForMoreThanOne(int $qty, int $discountCents)
    {
        $item = $this->createMock(BasketItem::class);
        $item->method('price')->willReturn(Money::USD(3295*$qty));
        $item->method('qty')->willReturn($qty);

        $basket = $this->createMock(Basket::class);
        $basket->method('getItemFromBasketBySku')->willReturn($item);

        $discount = $this->offer->apply($basket);

        $this->assertTrue($discount->equals(Money::USD($discountCents)));
    }

    public function discountValues(): \Generator
    {
        for ($i=2; $i<50; $i++) {
            $discount = floor($i / 2) * ceil(3295 / 2);
            yield [$i, $discount];
        }
    }
}