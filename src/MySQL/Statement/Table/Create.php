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

        $primaryKeys = '';
        if (!empty($this->primaryKeys)) {
            $primaryKeys = ', PRIMARY KEY (' . implode(',', array_map([$this->connection, 'quoteClause'], $this->primaryKeys)) . ')';
        }

        $sql .= ' ( ' . implode(', ', $this->columns) . $primaryKeys . ' )';

        if (!empty($this->extras)) {
            $sql .= ' ' . $this->extras;
        }

        $sql .=  ' DEFAULT CHARSET='.$this->charset . ';';

        return $sql;
    }
}