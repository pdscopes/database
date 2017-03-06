<?php

namespace MadeSimple\Database;

/**
 * Class Column
 *
 * @package MadeSimple\Database
 * @author  Peter Scopes
 */
class Column
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
    protected $type;

    /**
     * @var string
     */
    protected $extras;

    /**
     * Column constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $name
     *
     * @return static
     */
    public function name($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return static
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param string $extras
     *
     * @return static
     */
    public function extras($extras)
    {
        $this->extras = $extras;

        return $this;
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->connection->quoteColumn($this->name) . ' ' . $this->type . ' ' . $this->extras;
    }
}