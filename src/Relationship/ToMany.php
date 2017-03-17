<?php

namespace MadeSimple\Database\Relationship;

use MadeSimple\Database\Entity;
use MadeSimple\Database\Relationship;

/**
 * Class ToMany
 *
 * @package MadeSimple\Database\Relationship
 * @author  Peter Scopes
 */
class ToMany extends Relationship
{

    /**
     * @return Entity[]
     */
    public function fetch()
    {
        $statement = $this->query()->execute();

        if (!$statement) {
            return null;
        }

        $items = [];
        while (($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $items[] = new $this->relation($this->entity->pool, $row);
        }

        return $items;
    }

}