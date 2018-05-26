<?php
namespace App;

use Antnee\Collection\Collection;

/**
 * Class BasketItemCollection
 *
 * @package App
 */
class BasketItemCollection extends Collection
{
    public function __construct(BasketItem ...$items)
    {
        foreach ($items as $item) {
            $this->append($item);
        }
    }

    public function append($basketItem)
    {
        if (!$basketItem instanceof BasketItem) {
            throw new \TypeError(sprintf(
                'Value passed to append method should be %s. %s given instead',
                BasketItem::class,
                is_object($basketItem) ? get_class($basketItem) : gettype($basketItem)
            ));
        }
        $this->offsetSet($basketItem->code(), $basketItem);
    }

    public function offsetSet($sku, $basketItem)
    {
        if (!$basketItem instanceof BasketItem) {
            throw new \TypeError(sprintf(
                'Value passed to offsetSet method should be %s. %s given instead',
                BasketItem::class,
                is_object($basketItem) ? get_class($basketItem) : gettype($basketItem)
            ));
        }
        parent::offsetSet($sku, $basketItem);
    }
}