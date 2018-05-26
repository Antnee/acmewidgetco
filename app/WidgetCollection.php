<?php
namespace App;

use Antnee\Collection\Collection;

/**
 * Class WidgetCollection
 *
 * @package App
 */
class WidgetCollection extends Collection
{
    public function __construct(Widget ...$items)
    {
        foreach ($items as $item) {
            $this->append($item);
        }
    }

    public function append($widget)
    {
        if (!$widget instanceof Widget) {
            throw new \TypeError(sprintf(
                'Value passed to append method should be %s. %s given instead',
                Widget::class,
                is_object($widget) ? get_class($widget) : gettype($widget)
            ));
        }
        $this->offsetSet($widget->code(), $widget);
    }

    public function offsetSet($sku, $widget)
    {
        if (!$widget instanceof Widget) {
            throw new \TypeError(sprintf(
                'Value passed to offsetSet method should be %s. %s given instead',
                Widget::class,
                is_object($widget) ? get_class($widget) : gettype($widget)
            ));
        }
        parent::offsetSet($sku, $widget);
    }
}