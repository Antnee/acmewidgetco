<?php
namespace App;

use Antnee\Collection\Collection;

/**
 * Class WidgetFsProvider
 *
 * @package App
 */
class WidgetFsProvider implements WidgetProvider
{
    /** @var WidgetProvider */
    private $attached;

    /** @var Collection */
    private $catalog;

    public function __construct(string $catalog)
    {
        $jsonData = json_decode($catalog, false, 512, JSON_BIGINT_AS_STRING);
        if (null === $jsonData) {
            throw new \InvalidArgumentException("Catalog is not valid JSON");
        }
        $this->catalog = collect($jsonData);
    }

    /**
     * Attach a WidgetProvider
     *
     * This provider supports chaining of additional providers, in order to implement a simple interface, yet allow us
     * to try to retrieve the data from multiple sources.
     *
     * @param WidgetProvider $provider
     */
    public function attach(WidgetProvider $provider): void
    {
        $this->attached = $provider;
    }

    /**
     * Get Full Widget Catalogue
     *
     * @return Collection
     * @throws \Exception
     */
    public function getAll(): Collection
    {
        if (count($this->catalog)) {
            return $this->catalog;
        }
        if ($this->attached) {
            return $this->attached->getAll();
        }
        throw new \Exception("Missing catalog data");
    }

    /**
     * Get Widget by SKU
     *
     * @param string $sku
     * @return object
     * @throws \Exception If no widget can be found
     */
    public function getBySku(string $sku): object
    {
        $widgetData = $this->catalog->first(function ($data) use ($sku) {
            if ($sku === $data->code) {
                return $data;
            }
        });
        if (!$widgetData && $this->attached) {
            return $this->attached->getBySku($sku);
        }
        if (!$widgetData) {
            throw new \Exception(sprintf("Widget with SKU '%s' could not be found", $sku));
        }

        return $widgetData;
    }
}