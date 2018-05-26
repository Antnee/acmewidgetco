<?php
namespace Tests;

use App\WidgetCollection;
use App\WidgetProvider;
use App\WidgetRepository;
use Money\Money;
use PHPUnit\Framework\TestCase;

class WidgetRepositoryTest extends TestCase
{
    /** @var WidgetRepository */
    private $repo;

    public function setUp()
    {
        parent::setUp();
        $provider = $this->getMockProvider();
        $this->repo = new WidgetRepository($provider);
    }

    private function getMockProvider($valid=true)
    {
        $provider = $this->createMock(WidgetProvider::class);
        $data = collect(json_decode(
            $this->catalogData(),
            false,
            512,
            JSON_BIGINT_AS_STRING
        ));
        $provider->method('getAll')->willReturn($data);
        if ($valid) {
            $provider->method('getBySku')->willReturn((object)[
                "name" => "Green Widget",
                "code" => "G01",
                "price" => "24.95",
                "currency" =>"USD"
            ]);
        } else {
            $provider->method('getBySku')->willReturn((object)[
                "name" => "Invalid Widget",
                "code" => "INV",
                "price" => "12.34",
                "currency" =>"GBP"
            ]);
        }

        return $provider;
    }

    /**
     * @test
     */
    public function getAllWidgets()
    {
        $this->assertInstanceOf(WidgetCollection::class, $this->repo->getAllWidgets());
    }

    /**
     * @test
     */
    public function getWidgetBySku()
    {
        $widget = $this->repo->getWidgetBySku('G01');
        $this->assertEquals('Green Widget', $widget->name());
        $this->assertEquals('G01', $widget->code());
        $this->assertTrue($widget->price()->equals(Money::USD('2495')));
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function invalidCurrency()
    {
        $provider = $this->getMockProvider(false);
        $repo = new WidgetRepository($provider);
        var_dump($repo->getWidgetBySku('INV'));
    }

    public function catalogData(): string
    {
        return '[
          {
            "name": "Red Widget",
            "code": "R01",
            "price": "32.95",
            "currency": "USD"
          },{
            "name": "Green Widget",
            "code": "G01",
            "price": "24.95",
            "currency": "USD"
          },{
            "name": "Blue Widget",
            "code": "B01",
            "price": "7.95",
            "currency": "USD"
          }
        ]';
    }
}