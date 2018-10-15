<?php

namespace MadeSimple\Database\Relationship;

use MadeSimple\Arrays\Collection;
use MadeSimple\Database\EntityCollection;
use MadeSimple\Database\Relationship;

class ToMany extends Relationship
{
    /**
     * @return \MadeSimple\Database\EntityCollection|\MadeSimple\Arrays\Collection
     */
    public function fetch()
    {
        /** @var \PDOStatement $statement */
        list($statement) = $this->query()->statement();

        if (!$statement) {
            return null;
        }

        if (class_exists($this->relation, false)) {
            $collection = new EntityCollection($statement->fetchAll(\PDO::FETCH_CLASS, $this->relation, [$this->entity->pool, true]));
            if (!empty($this->related)  && method_exists($this->relation, 'relate')) {
                foreach ($this->related as list($relative, $relationship, $args)) {
                    $collection->each(function ($entity) use ($relative, $relationship, $args) {
                        $entity->relate($relative, $relationship, $args);
                    });
                }
            }
            return $collection;
        } else {
            return new Collection($statement->fetchAll(\PDO::FETCH_OBJ));
        }
    }

    /**
     * @see \MadeSimple\Database\Query\Select::count()
     * @return int
     */
    public function count()
    {
        return $this->query->count();
    }
}