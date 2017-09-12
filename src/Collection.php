<?php

namespace MadeSimple\Database;

use MadeSimple\Arrays\Arr;
use MadeSimple\Arrays\ArrDots;
use MadeSimple\Database\Relationship\Relational;

class Collection extends \MadeSimple\Arrays\Collection
{

    /**
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
                    } elseif (method_exists($pointer, $segment) && $pointer instanceof Relational) {
                        $pointer = $pointer->relation($segment);
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
}