<?php

namespace MadeSimple\Database\Relationship;

use MadeSimple\Database\Entity;
use MadeSimple\Database\Relationship;

class ToOne extends Relationship
{
    /**
     * @return Entity|\stdClass
     */
    public function fetch()
    {
        /** @var \PDOStatement $statement */
        list($statement) = $this->query()->limit(1)->statement();

        if (!$statement) {
            return null;
        }

        if (class_exists($this->relation, false)) {
            $statement->setFetchMode(\PDO::FETCH_CLASS, $this->relation, [$this->entity->pool, true]);
            $entity = $statement->fetch();
        } else {
            $entity = $statement->fetch(\PDO::FETCH_OBJ);
        }

        return $entity ? $entity : null;
    }
}