<?php

namespace MadeSimple\Database;

use MadeSimple\Arrays\Arr;
use MadeSimple\Arrays\ArrDots;
use MadeSimple\Arrays\Collection;

class EntityCollection extends Collection implements Jsonable
{
    /**
     * Searches the collection for the entity that has the matching primary key.
     *
     * @param mixed  $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->isEmpty()) {
            return $default;
        }
        /** @var EntityMap $map */
        $map = $this->first()::map();
        return $this->find(function (Entity $entity) use ($key, $map) {
            return $entity->{$map->primaryKey(0)} === $key;
        }) ?? $default;
    }

    /**
     * Plucks the specified columns from the collection.
     *
     * @param string|array $columns
     *
     * @return static
     */
    public function pluck($columns)
    {
        $columns = is_array($columns) ? $columns : func_get_args();
        $single  = count($columns) === 1;

        $plucked = [];
        /** @var Entity $entity */
        foreach ($this->items as $entity) {
            $extracted = [];
            foreach ($columns as $key) {
                $pointer = &$entity;
                foreach (explode('.', $key) as $segment) {
                    if (is_array($pointer)) {
                        $pointer = &$pointer[$segment];
                    } elseif (property_exists($pointer, $segment)) {
                        $pointer = &$pointer->{$segment};
                    } elseif (method_exists($pointer, $segment) && method_exists($pointer, 'relation')) {
                        $relation = $pointer->relation($segment);
                        $pointer  = &$relation;
                    } else {
                        throw new \RuntimeException('Could not find ' . $key);
                    }
                }

                ArrDots::set($extracted, $key, $pointer);
            }


            if ($single) {
                $extracted = Arr::flatten($extracted);
                $plucked[] = reset($extracted);
            } else {
                $plucked[] = $extracted;
            }
        }

        return new static($plucked);
    }

    /**
     * Filters the collection keeping entities that match the where clause
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     *
     * @return static
     */
    public function where($column, $operator, $value)
    {
        return $this->filter(function (Entity $entity) use ($column, $operator, $value) {
            switch ($operator) {
                case '>':
                    return $entity->{$column} > $value;
                case '>=':
                    return $entity->{$column} >= $value;

                case '<':
                    return $entity->{$column} < $value;
                case '<=':
                    return $entity->{$column} <= $value;

                case '!==':
                    return $entity->{$column} !== $value;

                case '===':
                    return $entity->{$column} === $value;

                case '!=':
                    return $entity->{$column} != $value;

                case '==':
                default:
                    return $entity->{$column} == $value;
            }
        });
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
    public function __toString()
    {
        return join('', array_map(function ($i) {
            return is_array($i) ? array_map('strval', $i) :  (string) $i;
        }, $this->toArray()));
    }
}
