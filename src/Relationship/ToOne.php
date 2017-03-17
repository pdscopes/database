<?php

namespace MadeSimple\Database\Relationship;

use MadeSimple\Database\Entity;
use MadeSimple\Database\Relationship;

/**
 * Class ToOne
 *
 * @package MadeSimple\Database\Relationship
 * @author  Peter Scopes
 */
class ToOne extends Relationship
{
    /**
     * @return Entity
     */
    public function fetch()
    {
        $statement = $this->query()->limit(1)->execute();

        if (!$statement) {
            return null;
        }

        if (($row = $statement->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_FIRST))) {
            return new $this->relation($this->entity->pool, $row);
        }

        return null;
    }

}