<?php
namespace App;

use Money\Money;

class DeliveryRuleClient
{
    private $rules;

    public function __construct(DeliveryRule $rules)
    {
        $this->rules = $rules;
    }

    /**
     * Calculate Delivery Price
     *
     * @param Basket $basket
     * @throws \Exception When no rules can be matched
     * @return Money
     */
    public function deliveryPrice(Basket $basket): Money
    {
        return $this->rules->getDeliveryPrice($basket);
    }
}