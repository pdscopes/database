<?php

namespace MadeSimple\Database\Statement\Query;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Statement\Query;

/**
 * Class Update
 *
 * @package MadeSimple\Database\Statement\Query
 * @author  Peter Scopes
 */
class Update extends Query
{
    use WhereTrait;

    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string
     */
    protected $tableAlias;

    /**
     * @var array
     */
    protected $columns;

    /**
     * Update constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        $this->columns = [];
        $this->where   = new Clause($connection);
    }

    /**
     * @param string      $table Database table name
     * @param null|string $alias Alias for the table name
     *
     * @return static
     */
    public function table($table, $alias = null)
    {
        $this->tableName  = $table;
        $this->tableAlias = $alias ? : $table;

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return static
     */
    public function set($column, $value)
    {
        $this->columns[$column] = ':' . $column;
        $this->setParameter($column, $value);

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
        array_walk_recursive($columns, function ($v) { $this->columns[$v] = '?'; });


        return $this;
    }

    /**
     * @param array ...$values
     *
     * @return static
     */
    public function values(... $values)
    {
        $flattened = [];
        array_walk_recursive($values, function ($v) use (&$flattened) { $flattened[] = $v; });

        return $this->setParameters($flattened);
    }

    /**
     * {@InheritDoc}
     * If successful, clears the parameters.
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
        $sql = 'UPDATE ';

        // Set the table
        $table = $this->connection->quoteColumn($this->tableName);
        $alias = $this->connection->quoteColumn($this->tableAlias);
        $sql .= $alias == $table ? $table : $table . ' AS ' . $alias;

        // Set the SET
        $sql .= "\nSET\n";
        $sql .= implode(",\n", array_map(function ($col, $val) {
            return $this->connection->quoteColumn($col) . ' = ' . $val;
        }, array_keys($this->columns), $this->columns));

        // Add possible where clause(s)
        if (!$this->where->isEmpty()) {
            $sql .= "\nWHERE " . $this->where->flatten();
        }

        return $sql;
    }
}