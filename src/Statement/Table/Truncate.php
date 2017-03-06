<?php

namespace MadeSimple\Database\Statement\Table;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Statement;

/**
 * Class Truncate
 *
 * @package MadeSimple\Database\Statement\Table
 * @author  Peter Scopes
 */
class Truncate implements Statement
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
     * Drop constructor.
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
     * @return string
     */
    public function toSql()
    {
        return 'TRUNCATE TABLE ' . $this->name;
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