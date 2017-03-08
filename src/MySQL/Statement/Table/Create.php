<?php

namespace MadeSimple\Database\MySQL\Statement\Table;

use MadeSimple\Database\Column;

/**
 * Class Create
 *
 * @package MadeSimple\Database\MySQL\Statement\Table
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
        $sql = 'CREATE TABLE ' . $this->name;

        $sql .= " ( " . implode(', ', $this->columns) . " )";

        $sql .= " " . 'DEFAULT CHARSET='.$this->charset . ';';

        return $sql;
    }
}