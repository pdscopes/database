<?php

namespace MadeSimple\Database\Relation;

/**
 * Class HasOne
 *
 * @package MadeSimple\Database\Relation
 * @author  Peter Scopes
 */
class HasOne extends HasMany
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