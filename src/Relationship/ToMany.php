<?php

namespace MadeSimple\Database\Relationship;

use MadeSimple\Arrays\Collection;
use MadeSimple\Database\EntityCollection;
use MadeSimple\Database\Relationship;

class ToMany extends Relationship
{
    /**
     * @return EntityCollection|Collection
     */
    public function fetch()
    {
        /** @var \PDOStatement $statement */
        list($statement) = $this->query()->statement();

        if (!$statement) {
            return null;
        }

        if (class_exists($this->relation, false)) {
            return new EntityCollection($statement->fetchAll(\PDO::FETCH_CLASS, $this->relation, [$this->entity->pool, true]));
        } else {
            return new Collection($statement->fetchAll(\PDO::FETCH_OBJ));
        }
    }
}