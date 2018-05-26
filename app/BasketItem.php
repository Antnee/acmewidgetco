<?php
namespace App;

use Money\Money;

/**
 * Class BasketItem
 *
 * @package app
 */
class BasketItem
{
    private $widget;
    private $qty;

    public function __construct(Widget $widget, int $qty = 1)
    {
        $this->widget = $widget;
        if ($qty <= 0) {
            throw new \Exception("You cannot order a negative number of items");
        }
        $this->qty = $qty;
    }

    /**
     * Get Basket Item Quantity
     *
     * @return int
     */
    public function qty(): int
    {
        return $this->qty;
    }

    /**
     * Update the quantity of this basket item
     *
     * NOTE: BasketItem objects are immutable. You will receive a new copy that you must replace in the basket
     *
     * @param int $qty
     * @return BasketItem
     * @throws \Exception
     */
    public function withQty(int $qty) {
        if ($qty <= 0) {
            throw new \Exception("You cannot order a negative number of items");
        }
        $clone = clone $this;
        $clone->qty = $qty;
        return $clone;
    }

    public function code(): string
    {
        return $this->widget->code();
    }

    /**
     * Basket Item Price
     *
     * Returns a money object with the price multiplied by the quantity
     *
     * @return Money
     */
    public function price(): Money
    {
        return $this->widget->price()->multiply($this->qty);
    }
}