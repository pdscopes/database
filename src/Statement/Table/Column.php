<?php

namespace MadeSimple\Database\Statement\Table;

use MadeSimple\Database\Connection;

/**
 * Class Column
 *
 * @package MadeSimple\Database\Statement\Table
 * @author  Peter Scopes
 */
abstract class Column
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
     * @var  string
     */
    protected $dataType;

    /**
     * CreateTableColumn constructor.
     *
     * @param Connection $connection
     * @param string     $name
     */
    function __construct(Connection $connection, $name)
    {
        $this->connection = $connection;
        $this->name($name);
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function name($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    abstract function __toString();
}