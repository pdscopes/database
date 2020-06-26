<?php

namespace MadeSimple\Database;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use MadeSimple\Arrays\Arrayable;
use MadeSimple\Arrays\Collection;
use Traversable;


class PaginatedCollection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Jsonable
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var int
     */
    protected $page;

    /**
     * @var int
     */
    protected $total;

    /**
     * PaginatedCollection constructor.
     *
     * @param array $items
     * @param int   $page
     * @param int   $total
     */
    function __construct($items, $page, $total)
    {
        $this->items = $this->extractCollectibleItems($items);
        $this->page  = (int) $page;
        $this->total = (int) $total;
    }

    /**
     * @param mixed $items
     * @return array
     */
    protected function extractCollectibleItems($items)
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof Collection) {
            return $items->all();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof JsonSerializable) {
            return $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }
        return (array) $items;
    }

    /**
     * @return int
     */
    public function page()
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function total()
    {
        return $this->total;
    }

    public function toJson(int $options = 0, int $depth = 512)
    {
        return json_encode($this->jsonSerialize(), $options, $depth);
    }

    public function jsonSerialize()
    {
        return [
            'page'  => $this->page,
            'total' => $this->total,
            'items' => $this->items,
        ];
    }

    public function __toString()
    {
        return json_encode($this->jsonSerialize());
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    public function count()
    {
        return count($this->items);
    }

    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}