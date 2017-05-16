<?php

namespace MadeSimple\Database\Statement\Table;

use MadeSimple\Database\Column;
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
     * @var string database|table
     */
    protected $type;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var Column[]
     */
    protected $columns;

    /**
     * @var string[]
     */
    protected $primaryKeys;

    /**
     * @var string
     */
    protected $extras;

    /**
     * @var string
     */
    protected $charset;

    /**
     * Table constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection  = $connection;
        $this->columns     = [];
        $this->primaryKeys = [];
        $this->charset     = 'utf8';
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
     * @return Column
     */
    public abstract function column($name);

    /**
     * @param string|array $keys
     */
    public function primaryKeys($keys)
    {
        $this->primaryKeys = (array) $keys;
    }

    /**
     * @param string $extras
     */
    public function extras($extras)
    {
        $this->extras = $extras;
    }

    /**
     * @param string $charset
     */
    public function charset($charset)
    {
        $this->charset = $charset;
    }

    /**
     * @return string
     */
    public abstract function toSql();

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