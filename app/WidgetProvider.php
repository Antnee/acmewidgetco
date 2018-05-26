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
    public function attach(WidgetProvider $provider): void;
    public function getAll(): Collection;
    public function getBySku(string $sku): object;
}