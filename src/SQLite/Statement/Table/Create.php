<?php

namespace MadeSimple\Database\SQLite\Statement\Table;

/**
 * Class Table
 *
 * @package MadeSimple\Database\SQLite\Statement\Table
 * @author  Peter Scopes
 */
class Create extends \MadeSimple\Database\Statement\Table\Create
{
    /**
     * @var bool
     */
    protected $ifNotExists;


    /**
     * @param $flag
     *
     * @return $this
     */
    function ifNotExists($flag)
    {
        $this->ifNotExists = $flag;
        return $this;
    }

    /**
     * @param string $name
     *
     * @return Column
     */
    function column($name)
    {
        $column = new Column($this->connection, $name);
        $this->columns[$name] = $column;

        return $column;
    }

    public  function toSql()
    {
        $sql = 'CREATE TABLE ' . $this->connection->quoteClause($this->name) . ' ( ' . implode(', ', $this->columns) . ' )';

        return $sql;
    }
}