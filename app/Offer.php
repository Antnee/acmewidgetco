<?php
namespace App;

use Money\Money;

/**
 * Interface Offer
 *
 * @package App
 */
interface Offer
{
    /**
     * Check if Offer is Applicable to this Basket
     *
     * @param Basket $basket
     * @return bool
     */
    public function applicable(Basket $basket): bool;

    /**
     * Apply Offer
     *
     * Applies the offer and returns the price after the offer
     *
     * @param Basket $basket
     * @return Money
     */
    public function apply(Basket $basket): Money;

    /**
     * Offer Details
     *
     * Get a human-readable description of what the offer is
     *
     * @return String
     */
    public function details(): String;

    /**
     * Next Offer
     *
     * Allows chaining of offers
     *
     * @param Offer $offer
     * @return Offer
     */
    public function next(Offer $offer): Offer;
}