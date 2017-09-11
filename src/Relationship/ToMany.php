<?php

namespace MadeSimple\Database\Relationship;

use MadeSimple\Database\Collection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\Relationship;

class ToMany extends Relationship
{
    /**
     * @return Collection
     */
    public function fetch()
    {
        /** @var \PDOStatement $statement */
        list($statement) = $this->query()->statement();

        if (!$statement) {
            return null;
        }

        $map   = null;
        $items = [];
        while (($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            /** @var Entity $entity */
            $entity  = new $this->relation($this->entity->pool);
            $map     = $map ?? $entity->getMap();
            $items[] = $entity->populate($row, $map);
        }

        return new Collection($items);
    }

}