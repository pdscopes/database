<?php

namespace MadeSimple\Database\QueryBuilder;

use MadeSimple\Database\Connection;

/**
 * Class Insert
 *
 * @package MadeSimple\Database\QueryBuilder
 * @author  Peter Scopes
 */
class Insert extends Statement
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var string[]
     */
    protected $columns;

    /**
     * Insert constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        $this->columns = [];
    }

    /**
     * @param string $table Database table to insert into
     *
     * @return static
     */
    public function into($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param array ...$columns columns to be updated
     *
     * @return static
     */
    public function columns(... $columns)
    {
        $this->columns = [];
        array_walk_recursive($columns, function ($e) { $this->columns[] = $e; });

        return $this;
    }

    /**
     * @param array ...$values
     *
     * @return static
     */
    public function values(... $values)
    {
        array_walk_recursive($values, function ($e) { $this->parameters[] = $e; });

        return $this;
    }

    /**
     * {@InheritDoc}
     * If successful, clears the values.
     */
    public function execute(array $parameters = null)
    {
        $statement = parent::execute($parameters);
        $this->parameters = [];

        return $statement;
    }

    /**
     * @return string
     */
    public function toSql()
    {
        $sql = 'INSERT ';

        // Set the table
        $sql .= 'INTO ' . $this->connection->quoteColumn($this->table);

        // Add the columns
        if (!empty($this->columns)) {
            $sql .= "\n(" . implode(',', array_map([$this->connection, 'quoteColumn'], array_unique($this->columns))) . ')';
        }

        // Add the values
        if (!empty($this->parameters)) {
            $sql .=
                "\nVALUES\n(" .
                implode("),\n(", array_fill(0, count($this->parameters) / count($this->columns), implode(',', array_fill(0, count($this->columns), '?')))) .
                ')';
        }

        return $sql;
    }
}