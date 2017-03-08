<?php

namespace MadeSimple\Database\Statement\Table;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Statement;

/**
 * Class Alter
 *
 * @package MadeSimple\Database\Statement\Table
 * @author  Peter Scopes
 */
class Alter implements Statement
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $action;

    /**
     * Table constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $name
     */
    public function table($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $action
     */
    public function action($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function toSql()
    {
        $sql = 'ALTER TABLE ' . $this->name;
        $sql .= ' ' . $this->action;
        return $sql;
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