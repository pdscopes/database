<?php

namespace MadeSimple\Database\Entity;

use MadeSimple\Arrays\Arrayable;
use MadeSimple\Database\EntityCollection;
use MadeSimple\Database\Entity;
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
     * @param string $relationship
     * @param array  $args
     *
     * @return EntityCollection|Entity
     */
    public function relation($relationship, ... $args)
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
     * @param string $relationship
     * @param array $args
     * @return bool|string
     */
    protected function generateKey($relationship, array $args = [])
    {
        try {
            return $relationship . join('', $args);
        } catch (\Throwable $e) {
            return false;
        }
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