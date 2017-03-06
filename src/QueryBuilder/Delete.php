<?php

namespace MadeSimple\Database\QueryBuilder;

use MadeSimple\Database\Connection;

/**
 * Class Delete
 *
 * @package MadeSimple\Database\QueryBuilder
 * @author  Peter Scopes
 */
class Delete extends Statement
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
     * Delete constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        $this->where = new Clause();
    }


    /**
     * @param string      $table Database table name
     * @param null|string $alias Alias for the table name
     *
     * @return static
     */
    public function from($table, $alias = null)
    {
        $this->tableName  = $table;
        $this->tableAlias = $alias ? : $table;

        return $this;
    }

    /**
     * @return string
     */
    public function toSql()
    {
        $sql = 'DELETE ';

        // Add the from tables
        $table = $this->connection->quoteColumn($this->tableName);
        $alias = $this->connection->quoteColumn($this->tableAlias);
        $sql .= $alias == $table ? 'FROM ' . $table : $alias . ' FROM ' . $table . ' AS ' . $alias;

        // Add possible where clause(s)
        if (!$this->where->isEmpty()) {
            $sql .= "\nWHERE " . $this->connection->quoteClause($this->where->flatten());
        }

        return $sql;
    }
}