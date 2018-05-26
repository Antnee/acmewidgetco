<?php
namespace Tests;

use App\Basket;
use App\DeliveryRule;
use App\DeliveryRuleClient;
use Money\Money;
use PHPUnit\Framework\TestCase;


class DeliveryRuleClientTest extends TestCase
{
    /**
     * @test
     */
    public function getDeliveryPriceTest()
    {
        $basket = $this->createMock(Basket::class);
        $rule = $this->createMock(DeliveryRule::class);
        $rule->method('getDeliveryPrice')->willReturn(Money::USD(500));

        $client = new DeliveryRuleClient($rule);

        $this->assertTrue($client->deliveryPrice($basket)->equals(Money::USD(500)));
    }
}