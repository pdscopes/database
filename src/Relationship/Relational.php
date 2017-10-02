<?php

namespace MadeSimple\Database\Relationship;

use MadeSimple\Arrays\Arrayable;
use MadeSimple\Database\EntityCollection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\EntityMap;

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
        // If the relationship has not already been fetched
        if (!array_key_exists($relationship, $this->relationships)) {
            if (!method_exists($this, $relationship)) {
                throw new \InvalidArgumentException('No such relationship: ' . $relationship);
            }

            $this->relationships[$relationship] = call_user_func_array([$this, $relationship], $args)->fetch();
        }

        return $this->relationships[$relationship];
    }

    /**
     * Start to define a new to one type relationship.
     * @return ToOne
     */
    protected function toOne()
    {
        if ($this instanceof Entity) {
            return new ToOne($this);
        }
        throw new \RuntimeException('Cannot create to one relationship for non-entities');
    }

    /**
     * Start to define a new to many type relationship.
     * @return ToMany
     */
    protected function toMany()
    {
        if ($this instanceof Entity) {
            return new ToMany($this);
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