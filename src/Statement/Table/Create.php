<?php

namespace MadeSimple\Database\Statement\Table;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Statement;

/**
 * Class Create
 *
 * @package MadeSimple\Database\Statement\Table
 * @author  Peter Scopes
 */
abstract class Create implements Statement
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
     * @var Column[]
     */
    protected $columns = [];

    /**
     * Table constructor.
     *
     * @param Connection $connection
     * @param string     $name
     */
    public function __construct(Connection $connection, $name)
    {
        $this->connection = $connection;

        $this->name($name);
    }

    /**
     * @param string $name
     */
    public function name($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $name
     *
     * @return \MadeSimple\Database\Statement\Table\Column
     */
    public abstract function column($name);

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