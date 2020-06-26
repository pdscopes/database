<?php

namespace MadeSimple\Database\Entity;

use MadeSimple\Arrays\Arrayable;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityCollection;
use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Relationship;

trait Relational
{
    use Entity\PropertiesToArrayTrait, Entity\CastPropertyTrait;

    /**
     * @var EntityCollection[]|Entity[]
     */
    protected $relationships = [];

    /**
     * @return EntityMap
     */
    public static abstract function map();

    /**
     * Fetch the relation named `$relationship`. Results are cached.
     *
     * @param string $relationship
     * @param mixed  ...$args
     *
     * @return EntityCollection|Entity
     */
    public function relation($relationship, ...$args)
    {
        // Attempt to convert $args to a string
        $key = $this->generateKey($relationship, $args);

        // If the relationship has not already been fetched
        if ($key === false || !array_key_exists($key, $this->relationships)) {
            if (!method_exists($this, $relationship)) {
                throw new \InvalidArgumentException('No such relationship: ' . $relationship);
            }

            $relationship = call_user_func_array([$this, $relationship], $args)->fetch();
            if ($key) {
                $this->relationships[$key] = $relationship;
            }
            return $relationship;
        }

        return $this->relationships[$key];
    }

    /**
     * Explicitly set the relative.
     *
     * @param mixed  $relative
     * @param string $relationship
     * @param mixed  ...$args
     *
     * @return static
     */
    public function relate($relative, $relationship, ...$args)
    {
        // Attempt to convert $args to a string
        $key = $this->generateKey($relationship, $args);

        // Store the relationship
        $this->relationships[$key] = $relative;

        return $this;
    }

    /**
     * @param string $relationship
     * @param array $args
     * @return bool|string
     */
    protected function generateKey($relationship, array $args = [])
    {
        try {
            return $relationship . $this->keyify($args);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Recursively convert the array $items into a key string.
     *
     * @param array $items
     * @return string
     */
    private function keyify(array $items)
    {
        $keys = [];
        foreach ($items as $item) {
            if ($item instanceof EntityCollection) {
                $keys[] = '[' . $this->keyify($item->all()) . ']';
            }
            else if ($item instanceof Entity) {
                $keys[] = get_class($item) . '(' . join('|', $item::map()->primaryKeys($item)) . ')';
            }
            else if (is_array($item)) {
                $keys[] = '[' . $this->keyify($item) . ']';
            }
            else {
                $keys[] = (string) $item;
            }
        }
        return join(',', $keys);
    }

    /**
     * Start to define a new to one type relationship.
     * @return Relationship\ToOne
     */
    protected function toOne()
    {
        if ($this instanceof Entity) {
            return new Relationship\ToOne($this);
        }
        throw new \RuntimeException('Cannot create to one relationship for non-entities');
    }

    /**
     * Start to define a new to many type relationship.
     * @return Relationship\ToMany
     */
    protected function toMany()
    {
        if ($this instanceof Entity) {
            return new Relationship\ToMany($this);
        }
        throw new \RuntimeException('Cannot create to many relationship for non-entities');
    }


    /**
     * @return array
     */
    public function toArray()
    {
        $properties = $this->propertiesToArray(static::map()->columnMap());

        foreach ($this->relationships as $property => $value) {
            if (in_array($property, $this->hidden)) {
                continue;
            }
            $properties[$property] = $value instanceof Arrayable ? $value->toArray() : $this->cast($property);
        }

        return $properties;
    }
}
