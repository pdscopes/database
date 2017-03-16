<?php

namespace MadeSimple\Database\Relation;

/**
 * Class BelongsToOne
 *
 * @package MadeSimple\Database\Relation
 * @author  Peter Scopes
 */
class BelongsToOne extends BelongsToMany
{
    /**
     * @return null|\MadeSimple\Database\Entity
     */
    public function fetch()
    {
        $statement = $this->query()->limit(1)->execute();

        if (!$statement) {
            return null;
        }

        if (($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            return new $this->relative($this->entity->pool, $row);
        }

        return null;
    }
}