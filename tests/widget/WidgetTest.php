<?php
namespace Tests;

use App\Widget;
use Money\Money;
use PHPUnit\Framework\TestCase;

class WidgetTest extends TestCase
{
    /**
     * @test
     *
     * @dataProvider widgetDataProvider
     * @param string $name
     * @param string $sku
     * @param Money $price
     */
    public function newWidget(string $name, string $sku, Money $price)
    {
        $widget = new Widget($name, $sku, $price);
        $this->assertEquals($name, $widget->name());
        $this->assertEquals($sku, $widget->code());
        $this->assertEquals($price, $widget->price());
    }

    public function widgetDataProvider()
    {
        return [
            ['Test Widget 1', 'TEST1', Money::USD(1234)],
            ['Test Widget 2', 'TEST2', Money::USD(5678)],
            ['Test Widget 3', 'TEST3', Money::USD(123)],
            ['Test Widget 4', 'TEST4', Money::USD("12345678")],
        ];
    }
}