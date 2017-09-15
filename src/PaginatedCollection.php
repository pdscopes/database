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
        $this->page  = $page;
        $this->total = $total;
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


    /**
     * @InheritDoc
     */
    public function toJson($options = 0, $depth = 512)
    {
        return json_encode($this->jsonSerialize(), $options, $depth);
    }
    /**
     * @InheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'page'  => $this->page,
            'total' => $this->total,
            'items' => $this->items,
        ];
    }
    /**
     * @InheritDoc
     */
    public function __toString()
    {
        return json_encode($this->jsonSerialize());
    }
    /**
     * @InheritDoc
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->items);
    }
    /**
     * @InheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }
    /**
     * @InheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }
    /**
     * @InheritDoc
     */
    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }
    /**
     * @InheritDoc
     */
    public function count()
    {
        return count($this->items);
    }
    /**
     * @InheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }
}