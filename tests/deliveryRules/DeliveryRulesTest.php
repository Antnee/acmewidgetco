<?php
namespace Tests;

use App\Basket;
use App\DeliveryRule as When;
use Money\Money;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class DeliveryRulesTest
 *
 * WARNING: These tests use a generator that multiplies each test by 106
 *
 * @package Tests
 */
class DeliveryRulesTest extends TestCase
{
    /** @var MockObject */
    private $basket;

    public function setUp()
    {
        parent::setUp();
        $this->basket = $this->createMock(Basket::class);
        $this->basket->method('discountValue')->willReturn(Money::USD(0));

        $freeOverTenDollars = When::basketPriceGreaterThan(Money::USD(1000), Money::USD(0))
            ->then(When::basketPriceAnything(Money::USD(500)));
        $defaultPricing = When::basketPriceLessThan(Money::USD(5000), Money::USD(495))
            ->then(When::basketPriceLessThan(Money::USD(9000), Money::USD(295)))
            ->then(When::basketPriceAnything(Money::USD(0)));
    }

    /**
     * @test
     * @dataProvider allPricesDataProvider
     * @param int $priceInCents
     * @throws \Exception
     */
    public function allPricesAreTheSame(int $priceInCents)
    {
        $this->basket->method('basketItemsPrice')->willReturn(Money::USD($priceInCents));
        $rulePrice = Money::USD(500);

        $rule = When::basketPriceAnything($rulePrice);

        $this->assertTrue($rule->getDeliveryPrice($this->basket)->equals($rulePrice));
    }

    /**
     * @test
     * @dataProvider allPricesDataProvider
     * @param int $priceInCents
     * @throws \Exception
     */
    public function freeDeliveryOnOrdersOverTenDollarsUsingGreaterThan(int $priceInCents)
    {
        $basketPrice = Money::USD($priceInCents);
        $this->basket->method('basketItemsPrice')->willReturn($basketPrice);

        $rule = When::basketPriceGreaterThan(Money::USD(1000), Money::USD(0))
            ->then(When::basketPriceAnything(Money::USD(500)));

        $deliveryPrice = $rule->getDeliveryPrice($this->basket);

        if ($priceInCents > 1000) {
            $this->assertTrue($deliveryPrice->equals(Money::USD(0)));
        } else {
            $this->assertTrue($deliveryPrice->equals(Money::USD(500)));
        }
    }

    /**
     * @test
     * @dataProvider allPricesDataProvider
     * @param int $priceInCents
     * @throws \Exception
     */
    public function freeDeliveryOnOrdersOverTenDollarsUsingLessThan(int $priceInCents)
    {
        $basketPrice = Money::USD($priceInCents);
        $this->basket->method('basketItemsPrice')->willReturn($basketPrice);

        $rule = When::basketPriceLessThan(Money::USD(1000), Money::USD(500))
            ->then(When::basketPriceAnything(Money::USD(0)));

        $deliveryPrice = $rule->getDeliveryPrice($this->basket);

        if ($priceInCents > 1000) {
            $this->assertTrue($deliveryPrice->equals(Money::USD(0)));
        } else {
            $this->assertTrue($deliveryPrice->equals(Money::USD(500)));
        }
    }

    /**
     * @test
     * @dataProvider allPricesDataProvider
     * @param int $priceInCents
     * @throws \Exception
     */
    public function freeDeliveryBetweenFiveAndTenDollars(int $priceInCents)
    {
        $basketPrice = Money::USD($priceInCents);
        $this->basket->method('basketItemsPrice')->willReturn($basketPrice);

        $rule = When::basketPriceBetween(Money::USD(500), Money::USD(1000), Money::USD(0))
            ->then(When::basketPriceAnything(Money::USD(500)));

        $deliveryPrice = $rule->getDeliveryPrice($this->basket);

        if ($priceInCents >= 500 && $priceInCents <= 1000) {
            $this->assertTrue($deliveryPrice->equals(Money::USD(0)));
        } else {
            $this->assertTrue($deliveryPrice->equals(Money::USD(500)));
        }
    }

    /**
     * @test
     * @dataProvider allPricesDataProvider
     * @param int $priceInCents
     * @throws \Exception
     */
    public function freeDeliveryForExactlyNineFifty(int $priceInCents)
    {
        $basketPrice = Money::USD($priceInCents);
        $this->basket->method('basketItemsPrice')->willReturn($basketPrice);

        $rule = When::basketPriceEqualTo(Money::USD(950), Money::USD(0))
            ->then(When::basketPriceAnything(Money::USD(500)));

        $deliveryPrice = $rule->getDeliveryPrice($this->basket);

        if ($priceInCents === 950) {
            $this->assertTrue($deliveryPrice->equals(Money::USD(0)));
        } else {
            $this->assertTrue($deliveryPrice->equals(Money::USD(500)));
        }
    }

    /**
     * @test
     * @dataProvider allPricesDataProvider
     * @param int $priceInCents
     * @expectedException \Exception
     * @throws \Exception
     */
    public function noMatchingRules(int $priceInCents)
    {
        $basketPrice = Money::USD($priceInCents);
        $this->basket->method('basketItemsPrice')->willReturn($basketPrice);

        $rule = When::basketPriceGreaterThan(Money::USD(50000), Money::USD(0));

        $rule->getDeliveryPrice($this->basket);
    }

    /**
     * Three Chained Rules
     *
     * This test confirms that the provided example in the challenge is met:
     *
     * To  incentivise customers to spend more, delivery costs are reduced based on the amount spent. Orders under $50
     * cost $4.95. For orders under $90, delivery costs $2.95. Orders over $90 have free delivery.
     *
     * @test
     * @dataProvider allPricesDataProvider
     * @param int $priceInCents
     * @throws \Exception
     */
    public function threeChainedRules(int $priceInCents)
    {
        $basketPrice = Money::USD($priceInCents);
        $this->basket->method('basketItemsPrice')->willReturn($basketPrice);

        $rule = When::basketPriceLessThan(Money::USD(5000), Money::USD(495))
            ->then(When::basketPriceLessThan(Money::USD(9000), Money::USD(295)))
            ->then(When::basketPriceAnything(Money::USD(0)));

        /** @var Money $deliveryPrice */
        $deliveryPrice = $rule->getDeliveryPrice($this->basket);

        /** @var Money $deliveryPriceShouldBe */
        $deliveryPriceShould = Money::USD(0);

        if ($priceInCents < 5000) {
            $deliveryPriceShould = Money::USD(495);
        } elseif ($priceInCents < 9000) {
            $deliveryPriceShould = Money::USD(295);
        }

        $this->assertTrue($deliveryPriceShould->equals($deliveryPrice));
    }

    /**
     * Prices Data Provider
     *
     * Note that this provider will return every integer between 0 and 10,000 in increments of 95. This should be used
     * to check a range of example rules but be aware that 106 tests will run as a result of using this provider
     *
     * @return \Generator
     */
    public function allPricesDataProvider(): \Generator
    {
        for ($i=0; $i<10000; $i=$i+95) {
            yield [$i];
        }
    }
}