<?php
namespace App;
use Money\Money;

/**
 * Class OfferNull
 *
 * No offer
 *
 * @package App
 */
class OfferNull implements Offer
{
    public function applicable(Basket $basket): bool
    {
        return false;
    }

    public function apply(Basket $basket): Money
    {
        return Money::USD(0);
    }

    public function details(): String
    {
        return '';
    }

    public function next(Offer $offer): Offer
    {
        return $this;
    }

}