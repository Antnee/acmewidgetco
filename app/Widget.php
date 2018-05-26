<?php
namespace App;

use Money\Money;

/**
 * Class Widget
 *
 * @package App
 */
class Widget
{
    private $name;
    private $code;
    private $price;

    /**
     * Widget constructor
     *
     * @param string $name The name of the widget
     * @param string $code The SKU of the widget
     * @param Money $price The price (currency is encapsulated)
     */
    public function __construct(string $name, string $code, Money $price)
    {
        $this->name = $name;
        $this->code = $code;
        $this->price = $price;
    }

    /**
     * Widget Name
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Widget SKU
     *
     * @return string
     */
    public function code(): string
    {
        return $this->code;
    }

    /**
     * Widget Price
     *
     * @return Money
     */
    public function price(): Money
    {
        return $this->price;
    }
}