<?php

namespace MadeSimple\Database\Relationship;

use MadeSimple\Database\Entity;
use MadeSimple\Database\Relationship;

class ToOne extends Relationship
{
    /**
     * @return Entity
     */
    public function fetch()
    {
        /** @var \PDOStatement $statement */
        list($statement) = $this->query()->limit(1)->statement();

        if (!$statement) {
            return null;
        }

        if (($row = $statement->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_FIRST))) {
            return new $this->relation($this->entity->pool, $row);
        }

        return null;
    }

}