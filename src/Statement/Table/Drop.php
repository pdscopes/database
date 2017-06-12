<?php

namespace MadeSimple\Database\Statement\Table;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Statement;

/**
 * Class Drop
 *
 * @package MadeSimple\Database\Statement\Table
 * @author  Peter Scopes
 */
class Drop implements Statement
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $table;

    /**
     * Drop constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Creates SQL: DROP TABLE $name
     *
     * @param string $name
     *
     * @return static
     */
    public function table($name)
    {
        $this->table = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function toSql()
    {
        return 'DROP TABLE ' . $this->connection->quoteClause($this->table);
    }

    /**
     * @param array|null $parameters
     *
     * @return \PDOStatement
     */
    public function execute(array $parameters = null)
    {
        return $this->connection->query($this->toSql());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toSql();
    }
}