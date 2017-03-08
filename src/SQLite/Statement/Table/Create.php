<?php

namespace MadeSimple\Database\SQLite\Statement\Table;

use MadeSimple\Database\Column;

/**
 * Class Table
 *
 * @package MadeSimple\Database\SQLite\Statement\Table
 * @author  Peter Scopes
 */
class Create extends \MadeSimple\Database\Statement\Table\Create
{
    /**
     * @param string $name
     *
     * @return Column
     */
    public  function column($name)
    {
        $column = new Column($this->connection);
        $this->columns[] = $column;

        return $column->name($name);
    }

    public  function toSql()
    {
        $sql = 'CREATE TABLE ' . $this->name . ' ( ' . implode(', ', $this->columns) . ' )';

        return $sql;
    }
}