<?php
namespace Tests;

use Antnee\Collection\Collection;
use App\WidgetFsProvider;
use App\WidgetProvider;
use PHPUnit\Framework\TestCase;

class WidgetProviderTest extends TestCase
{
    /** @var WidgetProvider */
    private $provider;
    private $attachedProvider;
    private $emptyProvider;

    public function setUp()
    {
        parent::setUp();
        $this->provider = new WidgetFsProvider(file_get_contents(realpath(WIDGET_CATALOG_JSON)));
        $this->emptyProvider = new WidgetFsProvider('[]');
        $this->attachedProvider = new WidgetFsProvider('[]');
        $this->attachedProvider->attach($this->provider);
    }

    /**
     * Provider Type Check
     *
     * @test
     */
    public function providerType()
    {
        $this->assertInstanceOf(WidgetProvider::class, $this->provider);
    }

    /**
     * @test
     */
    public function getAll()
    {
        $this->assertInstanceOf(Collection::class, $this->provider->getAll());
    }

    /**
     * @test
     */
    public function getAllFromAttachedProvider()
    {
        $this->assertInstanceOf(Collection::class, $this->attachedProvider->getAll());
        $this->assertGreaterThan(0, count($this->attachedProvider->getAll()));
    }

    /**
     * @test
     * @expectedException \Exception
     */
    public function getFromEmptyProvider()
    {
        $this->emptyProvider->getAll();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function invalidProviderConstructor()
    {
        new WidgetFsProvider('');
    }

    /**
     * @test
     * @dataProvider validWidgetDataProvider
     * @param string $name
     * @param string $sku
     * @param string $price
     * @param strinG $currency
     */
    public function getSpecificSku(string $name, string $code, string $price, string $currency)
    {
        $widget = $this->provider->getBySku($code);
        $this->assertInternalType('object', $widget);
        $this->assertEquals($code, $widget->code);
        $this->assertEquals($name, $widget->name);
        $this->assertEquals($price, $widget->price);
        $this->assertEquals($currency, $widget->currency);
    }

    /**
     * @test
     * @dataProvider invalidWidgetDataProvider
     * @expectedException \Exception
     * @param string $name
     * @param string $code
     * @param string $price
     * @param string $currency
     */
    public function getInvalidSku(string $name, string $code, string $price, string $currency)
    {
        $this->provider->getBySku($code);
    }

    /**
     * @test
     * @dataProvider validWidgetDataProvider
     * @param string $name
     * @param string $code
     * @param string $price
     * @param string $currency
     */
    public function getSpecificSkuFromAttachedProvider(string $name, string $code, string $price, string $currency)
    {
        $widget = $this->attachedProvider->getBySku($code);
        $this->assertInternalType('object', $widget);
        $this->assertEquals($code, $widget->code);
        $this->assertEquals($name, $widget->name);
        $this->assertEquals($price, $widget->price);
        $this->assertEquals($currency, $widget->currency);
    }

    /**
     * @return array
     */
    public function validWidgetDataProvider(): array
    {
        $catalog = file_get_contents(realpath(WIDGET_CATALOG_JSON));
        $this->assertNotFalse($catalog);
        return json_decode($catalog, true, 512, JSON_BIGINT_AS_STRING);
    }

    public function invalidWidgetDataProvider(): array
    {
        return [
            ['name'=>'invalid widget', 'code'=>'invalid', 'price'=>'not a price', 'currency'=>'polos'],
        ];
    }
}