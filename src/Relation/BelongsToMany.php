<?php

namespace MadeSimple\Database\Relation;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Entity;
use MadeSimple\Database\Relation;
use MadeSimple\Database\Statement\Query;

/**
 * Class BelongsToMany
 *
 * @package MadeSimple\Database\Relation
 * @author  Peter Scopes
 */
class BelongsToMany extends Relation
{

    /**
     * Initialise the relations query so that select columns and where clauses can be added to or replaced after the
     * construction. We delay the join onto the related table until query is called so that intermediate joins can be
     * added (allowing for relations through other related tables).
     *
     * @see Relation::query()
     * @param Connection $connection
     *
     * @return Query
     */
    protected function initialiseQuery(Connection $connection)
    {
        /** @var Entity $relative */
        $relative      = new $this->relative();
        $relativeTable = $relative->getMap()->tableName();
        $relativeAlias = null !== $this->relativeAlias ? $this->relativeAlias : $relativeTable;
        $entityMap     = $this->entity->getMap();

        // Select from the relative table
        $select = $connection->select()->columns($relativeAlias . '.*')->from($relativeTable, $relativeAlias);

        // Construct the where clause(s)
        foreach (array_keys($relative->getMap()->primaryKeys()) as $k => $dbColumn) {
            $value = $this->entity->{$entityMap->property($this->keys[$k])};
            $select->andWhere($relativeAlias . '.' . $dbColumn . ' = :' . $dbColumn, [$dbColumn => $value]);
        }

        return $select;
    }

    /**
     * @return null|Entity[]
     */
    public function fetch()
    {
        $statement = $this->query()->execute();

        if (!$statement) {
            return null;
        }

        $items = [];
        while (($row = $statement->fetch(\PDO::FETCH_ASSOC))) {
            $items[] = new $this->relative($this->entity->pool, $row);
        }

        return $items;
    }
}