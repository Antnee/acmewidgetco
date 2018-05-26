<?php
namespace App;

use Money\Money;

/**
 * Class DeliveryRule
 *
 * Runtime configuration of delivery rules. Use the named constructors; the __construct() method is private
 *
 * @package App
 */
class DeliveryRule
{
    /** @var DeliveryRule */
    private $next;

    /** @var Money */
    private $lessThan, $equalTo, $greaterThan, $price;

    private function __construct(Money $price, Money $lessThan=null, Money $greaterThan=null, Money $equalTo=null)
    {
        $this->price = $price;
        $this->lessThan = $lessThan;
        $this->greaterThan = $greaterThan;
        $this->equalTo = $equalTo;
    }

    /**
     * Basket Price is Anything
     *
     * Returns a rule that will match with ANY basket value
     *
     * @param Money $deliveryPrice
     * @return DeliveryRule
     */
    public static function basketPriceAnything(Money $deliveryPrice): self
    {
        return new self($deliveryPrice);
    }

    /**
     * Basket Price is EQUAL to
     *
     * Returns a rule that will match when a basket price is an exact match only
     *
     * @param Money $equalTo
     * @param Money $deliveryPrice
     * @return DeliveryRule
     */
    public static function basketPriceEqualTo(Money $equalTo, Money $deliveryPrice): self
    {
        return new self($deliveryPrice, null, null, $equalTo);
    }

    /**
     * Basket Price is GREATER than
     *
     * Returns a rule that will match when a basket price is GREATER than
     *
     * @param Money $greaterThan
     * @param Money $deliveryPrice
     * @return DeliveryRule
     */
    public static function basketPriceGreaterThan(Money $greaterThan, Money $deliveryPrice): self
    {
        return new self($deliveryPrice, null, $greaterThan);
    }

    /**
     * Basket Price is BETWEEN
     *
     * Returns a rule that will match when a basket price is BETWEEN two values. Not that the values are INCLUSIVE
     *
     * @param Money $lowerBound
     * @param Money $upperBound
     * @param Money $deliveryPrice
     * @return DeliveryRule
     */
    public static function basketPriceBetween(Money $lowerBound, Money $upperBound, Money $deliveryPrice): self
    {
        return new self($deliveryPrice, $upperBound, $lowerBound);
    }

    /**
     * Basket Price is LESS than
     *
     * Returns a rule that will match when a basket price is LESS than
     *
     * @param Money $lessThan
     * @param Money $deliveryPrice
     * @return DeliveryRule
     */
    public static function basketPriceLessThan(Money $lessThan, Money $deliveryPrice): self
    {
        return new self($deliveryPrice, $lessThan);
    }

    /**
     * Set Next Rule
     *
     * Chain additional rules together. Rules will be checked in order. For example:
     *
     * DeliveryRule::basketPriceLessThan(Money::USD(5000), Money(495))
     *  ->then(DeliveryRule::basketPriceLessThan(Money::USD(9000), Money(295))
     *  ->then(DeliveryRule::basketPriceAnything(Money(0));
     *
     * Is equivalent to:
     *
     * if ($basket->basketItemsPrice()->lessThan(Money::USD(5000))) {
     *     return Money::USD(495);
     * } elseif ($basket->basketItemsPrice()->lessThan(Money::USD(9000))) {
     *     return Money::USD(295);
     * }
     * return Money::USD(0);
     *
     * @param DeliveryRule $next
     * @return DeliveryRule
     */
    public function then(DeliveryRule $next): DeliveryRule
    {
        if ($this->next) {
            // Pass it on down the chain
            $this->next->then($next);
        } else {
            $this->next = $next;
        }
        return $this;
    }

    /**
     * Get Delivery Price
     *
     * Runs the delivery rule, including any attached rules, returning the delivery price. Will throw an \Exception if
     * it was not possible to match any rules
     *
     * @param Basket $basket
     * @throws \Exception If no rules matched
     * @return Money
     */
    public function getDeliveryPrice(Basket $basket): Money
    {
        $basketPrice = $basket->basketItemsPrice()->subtract($basket->discountValue());

        if ($this->equalTo) {
            // Check if an exact basket price has been set
            if ($basketPrice->equals($this->equalTo)) {
                return $this->price;
            }

        } elseif ($this->lessThan && $this->greaterThan) {
            // Check if a basket price range has been set
            if ($basketPrice->lessThan($this->lessThan) && $basketPrice->greaterThan($this->greaterThan)) {
                return $this->price;
            }

        } elseif ($this->lessThan) {
            // Check if ONLY a less-than price has been set
            if ($basketPrice->lessThan($this->lessThan)) {
                return $this->price;
            }

        } elseif ($this->greaterThan) {
            // Check if ONLY a greater-than price has been set
            if ($basketPrice->greaterThan($this->greaterThan)) {
                return $this->price;
            }

        } else {
            // Check when no bounds were defined
            return $this->price;
        }

        if ($this->next) {
            // If another rule was set, check it now
            return $this->next->getDeliveryPrice($basket);
        }

        // No rules matched and there are no remaining rules to run
        throw new \Exception('Unable to match delivery rules');
    }
}