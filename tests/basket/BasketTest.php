<?php
namespace Tests;

use App\Basket;
use App\DeliveryRuleClient;
use App\Offer;
use App\WidgetFsProvider;
use App\WidgetRepository;
use Money\Money;
use PHPUnit\Framework\TestCase;

class BasketTest extends TestCase
{
    /** @var Basket */
    private $basket;

    /** @var WidgetRepository */
    private $widgets;

    public function setUp()
    {
        parent::setUp();

        $this->widgets = new WidgetRepository(new WidgetFsProvider(file_get_contents(realpath(WIDGET_CATALOG_JSON))));
        $deliveryRuleClient = $this->createMock(DeliveryRuleClient::class);
        $deliveryRuleClient->method('deliveryPrice')->willReturn(Money::USD(500));

        $offer = $this->createMock(Offer::class);
        $offer->method('apply')->willReturn(Money::USD(0));

        $this->basket = new Basket($this->widgets,$deliveryRuleClient,$offer);
    }

    /**
     * @test
     */
    public function addBySku()
    {
        $this->assertFalse($this->basket->contains('G01'));

        $this->basket->add('G01');
        $this->assertTrue($this->basket->contains('G01'));
        $this->assertFalse($this->basket->contains('R01'));

        $this->basket->add('R01');
        $this->assertTrue($this->basket->contains('G01'));
        $this->assertTrue($this->basket->contains('R01'));
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function addUnknownBySku()
    {
        $this->basket->add('FAKE');
    }

    /**
     * This test confirms that we can ADD values and the qty increases accordingly
     *
     * @test
     */
    public function updateQtyWithAdd()
    {
        $this->basket->add('B01');
        $this->basket->add('G01');
        $this->assertEquals(1, $this->basket->getItemFromBasketBySku('G01')->qty());

        $this->basket = $this->basket->add('G01', 2);
        $this->assertEquals(3, $this->basket->getItemFromBasketBySku('G01')->qty());

        $this->basket = $this->basket->add('G01', 1);
        $this->assertEquals(4, $this->basket->getItemFromBasketBySku('G01')->qty());

        $this->basket = $this->basket->add('G01', -2); // You can add negative numbers as well
        $this->assertEquals(2, $this->basket->getItemFromBasketBySku('G01')->qty());

        $this->basket = $this->basket->add('G01', -2); // Remove them all
        try {
            $this->basket->getItemFromBasketBySku('G01');
            $this->fail('Exception not thrown when getting SKU not in the basket');
        } catch (\Exception $e) {}
    }

    /**
     * This test confirms that we can set an absolute qty
     * @test
     */
    public function updateQty()
    {
        $this->basket = $this->basket->add('B01');
        $this->basket = $this->basket->add('G01');
        $this->assertEquals(1, $this->basket->getItemFromBasketBySku('G01')->qty());

        $this->basket = $this->basket->updateQty('G01', 2);
        $this->assertEquals(2, $this->basket->getItemFromBasketBySku('G01')->qty());

        $this->basket = $this->basket->updateQty('G01', 1);
        $this->assertEquals(1, $this->basket->getItemFromBasketBySku('G01')->qty());
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function updateToZero()
    {
        $this->basket = $this->basket->add('B01', 3);
        $this->assertEquals(3, $this->basket->getItemFromBasketBySku('B01')->qty());
        $this->basket = $this->basket->updateQty('B01', 0);
        $this->basket->getItemFromBasketBySku('B01');
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function updateToNegative()
    {
        $this->basket = $this->basket->add('B01', 3);
        $this->assertEquals(3, $this->basket->getItemFromBasketBySku('B01')->qty());
        $this->basket = $this->basket->updateQty('B01', -2);
    }

    /**
     * @test
     */
    public function removeBySku()
    {
        $this->basket->add('B01', 5);
        $this->assertEquals(5, $this->basket->getItemFromBasketBySku('B01')->qty());
        $this->basket = $this->basket->remove('B01');

        try {
            $this->basket->getItemFromBasketBySku('B01');
            $this->fail('Expected exception not thrown');
        } catch (\Exception $e) {}

        $this->basket->add('B01', 2);
        $this->assertEquals(2, $this->basket->getItemFromBasketBySku('B01')->qty());
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function getUnknownItemBySku()
    {
        $this->basket->getItemFromBasketBySku('FAIL');
    }

    /**
     * @test
     */
    public function basketItemsPrice()
    {
        $this->assertInstanceOf(Money::class, $this->basket->basketItemsPrice());
        $this->assertTrue($this->basket->basketItemsPrice()->equals(Money::USD(0)));
        $this->basket->add('B01', 5);
        $rowPrice = $this->widgets->getWidgetBySku('B01')->price()->multiply(5);
        $this->assertTrue($this->basket->basketItemsPrice()->equals($rowPrice));
    }

    /**
     * @test
     */
    public function basketPrice()
    {
        $this->assertInstanceOf(Money::class, $this->basket->basketItemsPrice());
        $this->assertTrue($this->basket->basketPrice()->equals(Money::USD(500)));
        $this->basket->add('B01', 5);

        // Multiply row price, then add the delivery charge
        $basketPrice = $this->widgets->getWidgetBySku('B01')->price()->multiply(5)->add(Money::USD(500));
        $this->assertTrue($this->basket->basketPrice()->equals($basketPrice));
    }
}