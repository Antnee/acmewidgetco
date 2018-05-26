<?php
namespace _integration;

use App\Basket;
use App\DeliveryRule as When;
use App\DeliveryRuleClient;
use App\OfferRedWidgetBulk;
use App\WidgetFsProvider;
use App\WidgetRepository;
use Money\Money;
use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    /** @var Basket */
    private $basket;

    public function setUp()
    {
        parent::setUp();
        $widgetProvider = new WidgetFsProvider(file_get_contents(realpath(WIDGET_CATALOG_JSON)));
        $widgetRepo = new WidgetRepository($widgetProvider);

        $deliveryRules = When::basketPriceLessThan(Money::USD(5000), Money::USD(495))
            ->then(When::basketPriceLessThan(Money::USD(9000), Money::USD(295)))
            ->then(When::basketPriceAnything(Money::USD(0)));
        $delivery = new DeliveryRuleClient($deliveryRules);

        $offer = new OfferRedWidgetBulk;

        $this->basket = new Basket($widgetRepo, $delivery, $offer);
    }

    /**
     * @test
     * @dataProvider challengeScenarioDataProvider
     * @param Money $total
     * @param string ...$skus
     * @throws \Exception
     */
    public function challengeScenarios(Money $total, string ...$skus)
    {
        foreach ($skus as $sku) {
            $this->basket = $this->basket->add($sku);
        }
//        printf(
//            "\nSKUs:     %s\nTotal:    %s\nBasket:   %s\nItems:    %s\nDiscount: %s\nDelivery: %s\n",
//            implode(", ", $skus),
//            $total->getAmount(),
//            $this->basket->basketPrice()->getAmount(),
//            $this->basket->basketItemsPrice()->getAmount(),
//            $this->basket->discountValue()->getAmount(),
//            $this->basket->deliveryPrice()->getAmount()
//        );
        $this->assertTrue($this->basket->basketPrice()->equals($total));
    }

    public function challengeScenarioDataProvider(): array
    {
        return [
            [Money::USD(3785), 'B01', 'G01'],
            [Money::USD(5437), 'R01', 'R01'],
            [Money::USD(6085), 'R01', 'G01'],
            [Money::USD(9827), 'B01', 'B01', 'R01', 'R01', 'R01'],
        ];
    }
}