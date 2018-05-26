<?php
namespace App;

use Money\Money;

/**
 * WidgetRepository
 *
 * Construct with a WidgetProvider, which can have additional providers chained if necessary to support caching,
 * multiple data sources etc. The providers return simple objects and the repository will initialise the full
 * Widget object.
 *
 * In a real-world scenario, this repository would be completed with additional CRUD options, such as creating, updating
 * and deleting the Widgets from the catalog as required. By using the repository we can use the same logic here and
 * keep the providers down to just handling the operations on the data.
 *
 * @package App
 */
class WidgetRepository
{
    /** @var WidgetProvider */
    private $provider;

    public function __construct(WidgetProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Get Full Widget Catalog Collection
     *
     * @throws \Exception
     * @return WidgetCollection
     */
    public function getAllWidgets(): WidgetCollection
    {
        return $this->provider->getAll()->reduce(function (WidgetCollection $collection, $data) {
            $collection->append($this->initWidget($data));
            return $collection;
        }, new WidgetCollection);
    }

    /**
     * Get Specific Widget by SKU Code
     *
     * @param string $sku
     * @throws \Exception
     * @return Widget
     */
    public function getWidgetBySku(string $sku): Widget
    {
        return $this->initWidget($this->provider->getBySku($sku));
    }

    /**
     * Initialise Widget
     *
     * Takes a single widget from the catalog data and returns an appropriate widget
     *
     * @param object $data
     * @throws \Exception
     * @return Widget
     */
    private function initWidget(object $data): Widget
    {
        switch ($data->currency) {
            case 'USD':
                $price = Money::USD((int)($data->price * 100));
                break;
            default:
                throw new \Exception(sprintf("Unsupported currency: %s", $data->currency));
        }
        return new Widget($data->name, $data->code, $price);
    }
}