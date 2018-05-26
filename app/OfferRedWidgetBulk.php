<?php
namespace App;

use Money\Money;

/**
 * OfferRedWidgetBulk
 *
 * When more than one red widget (R01) is bought, half of them will be charged at half price
 *
 * @package App
 */
class OfferRedWidgetBulk implements Offer
{
    private $details = 'Buy one red widget, get the second half price';

    /** @var Offer */
    private $next;

    /** @var Basket */
    private $basket;

    public function applicable(Basket $basket): bool
    {
        try {
            $items = $basket->getItemFromBasketBySku('R01');
            if ($items->qty() > 1) {
                return true;
            }
        } catch (\Exception $e) {}
        return false;
    }

    public function apply(Basket $basket): Money
    {
        if (!$this->applicable($basket)) {
            // Not applicable
            if ($this->next) {
                // Try next offer
                return $this->next->apply($basket);
            }

            // No discount to apply
            return Money::USD(0);
        }

        // Calculate the discount to apply
        $items = $basket->getItemFromBasketBySku('R01');

        // Get the price for each item
        $each  = $items->price()->divide($items->qty(), Money::ROUND_HALF_UP);

        return $each->divide(2, Money::ROUND_HALF_UP)
            ->multiply(floor($items->qty() / 2));
    }

    public function details(): String
    {
        return $this->details;
    }

    public function next(Offer $offer): Offer
    {
        if ($this->next) {
            // Pass it on down the chain
            $this->next->next($offer);
        } else {
            $this->next = $offer;
        }
        return $this;
    }

}