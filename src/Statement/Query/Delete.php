<?php

namespace MadeSimple\Database\Statement\Query;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Statement\Query;

/**
 * Class Delete
 *
 * @package MadeSimple\Database\Statement\Query
 * @author  Peter Scopes
 */
class Delete extends Query
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
    public function __construct(Connection $connection = null)
    {
        parent::__construct($connection);

        $this->where = new Clause($connection);
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
        $table = $this->connection->quoteClause($this->tableName);
        $alias = $this->connection->quoteClause($this->tableAlias);
        $sql .= $alias == $table ? 'FROM ' . $table : $alias . ' FROM ' . $table . ' AS ' . $alias;

        // Add possible where clause(s)
        if (!$this->where->isEmpty()) {
            $sql .= ' WHERE ' . $this->where->flatten();
        }

        return $sql;
    }
}