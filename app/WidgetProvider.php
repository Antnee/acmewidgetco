<?php
namespace App;

use Antnee\Collection\Collection;

/**
 * Interface WidgetProvider
 *
 * @package App
 */
interface WidgetProvider
{
    /**
     * Chain a new WidgetProvider to this provider
     *
     * @param WidgetProvider $provider
     */
    public function attach(WidgetProvider $provider): void;

    /**
     * Get all Widgets
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Get Widget by SKU
     *
     * @param string $sku
     * @return object
     */
    public function getBySku(string $sku): object;
}