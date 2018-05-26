<?php
namespace Tests;

use App\Basket;
use App\Offer;
use App\OfferNull;
use Money\Money;
use PHPUnit\Framework\TestCase;


class OfferNullTest extends TestCase
{
    /** @var OfferNull */
    private $offer;

    public function setUp()
    {
        parent::setUp();
        $this->offer = new OfferNull();
    }

    /**
     * @test
     * @dataProvider intRangeProvider
     * @param int $priceInCents
     * @throws \Exception
     */
    public function apply(int $priceInCents)
    {
        $basket = $this->createMock(Basket::class);
        $basket->method('basketPrice')->willReturn(Money::USD($priceInCents));
        $this->assertFalse($this->offer->applicable($basket));
        $this->assertTrue($this->offer->apply($basket)->equals(Money::USD(0)));
        $this->assertInternalType('string', $this->offer->details());
        $this->assertInstanceOf(Offer::class, $this->offer->next($this->createMock(Offer::class)));
    }


    public function intRangeProvider(): \Generator
    {
        for ($i=0; $i<=5000; $i=$i+95) {
            yield [$i];
        }
    }
}