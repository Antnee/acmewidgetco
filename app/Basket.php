<?php
namespace App;

use Money\Money;

/**
 * Class Basket
 *
 * @package App
 */
class Basket
{
    private $offer;
    private $rules;
    private $widgets;
    private $basket;

    public function __construct(WidgetRepository $widgets, DeliveryRuleClient $rules, Offer $offer)
    {
        $this->widgets = $widgets;
        $this->rules = $rules;
        $this->offer = $offer;
        $this->basket = new BasketItemCollection;
    }

    /**
     * Basket Total Price
     *
     * @throws \Exception
     * @return Money
     */
    public function basketPrice(): Money
    {
        return $this->basketItemsPrice()->add($this->deliveryPrice())->subtract($this->discountValue());
    }

    /**
     * Basket Items Price
     *
     * Gets the price for all basket items. Excludes delivery
     *
     * @return Money
     */
    public function basketItemsPrice(): Money
    {
        return $this->basket->reduce(function(Money $carry, BasketItem $item) {
            return $item->price()->add($carry);
        }, Money::USD(0));
    }

    /**
     * Get Basket Delivery Price
     *
     * @return Money
     * @throws \Exception
     */
    public function deliveryPrice(): Money
    {
        return $this->rules->deliveryPrice($this);
    }

    /**
     * Get Discount Value
     *
     * @return Money
     */
    public function discountValue(): Money
    {
        return $this->offer->apply($this);
    }

    /**
     * Add a Widget by SKU
     *
     * @param string $sku
     * @param int $qty
     * @return Basket
     * @throws \Exception
     */
    public function add(string $sku, int $qty = 1): self
    {
        // Updates to SKUs already in the basket are handled by the updateQty() method
        if ($this->contains($sku)) {
            $requestedQty = $this->getItemFromBasketBySku($sku)->qty() + $qty;
            if (0 === $requestedQty) {
                return $this->remove($sku);
            }
            return $this->updateQty($sku, $requestedQty);
        }

        try {
            $widget = $this->widgets->getWidgetBySku($sku);
            $clone = clone $this;
            $clone->basket[$sku] = new BasketItem($widget,$qty);
            return $clone;
        } catch (\Exception $e) {
            throw new \Exception("The requested product code is not recognised", null, $e);
        }
    }

    /**
     * Update the quantity of a SKU
     *
     * @param string $sku
     * @param int $qty
     * @throws \Exception when a negative qty is requested
     * @return Basket
     */
    public function updateQty(string $sku, int $qty): self
    {
        if (0 === $qty) {
            return $this->remove($sku);
        }
        $clone = clone $this;
        $clone->basket = $this->basket->map(function (BasketItem $basketItem) use ($qty, $sku) {
            if ($basketItem->code() === $sku) {
                return $basketItem->withQty($qty);
            }
            return $basketItem;
        });
        return $clone;
    }

    /**
     * Remove Basket Item by SKU
     *
     * @param string $sku
     * @return Basket
     */
    public function remove(string $sku): self
    {
        $clone = clone $this;
        unset($clone->basket[$sku]);
        return $clone;
    }

    /**
     * Get Basket Item by SKU
     *
     * @param string $sku
     * @return BasketItem
     * @throws \Exception
     */
    public function getItemFromBasketBySku(string $sku): BasketItem
    {
        if ($this->contains($sku)) {
            return $this->basket[$sku];
        }
        throw new \Exception(sprintf("Requested SKU (%s) does not exist in the basket", $sku));
    }

    /**
     * Check if SKU is in the basket already
     *
     * @param string $sku
     * @return bool
     */
    public function contains(string $sku): bool
    {
        return isset($this->basket[$sku]);
    }
}
