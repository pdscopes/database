<?php

namespace MadeSimple\Database\Statement\Query;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Statement\Query;
use Psr\Log\LoggerInterface;

/**
 * Class Insert
 *
 * @package MadeSimple\Database\Statement\Query
 * @author  Peter Scopes
 */
class Insert extends Query
{
    /**
     * @var string
     */
    protected $table;

    /**
     * @var string[]
     */
    protected $columns;

    /**
     * Insert constructor.
     *
     * @param Connection|null $connection
     * @param LoggerInterface $logger
     */
    public function __construct(Connection $connection = null, LoggerInterface $logger)
    {
        parent::__construct($connection, $logger);

        $this->columns = [];
    }

    /**
     * @param string $table Database table to insert into
     *
     * @return static
     */
    public function into($table)
    {
        $this->table = $table;

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
        array_walk_recursive($columns, function ($e) { $this->columns[] = $e; });

        return $this;
    }

    /**
     * @param array ...$values
     *
     * @return static
     */
    public function values(... $values)
    {
        array_walk_recursive($values, function ($e) { $this->parameters[] = $e; });

        return $this;
    }

    /**
     * {@InheritDoc}
     * If successful, clears the values.
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
        $sql = 'INSERT ';

        // Set the table
        $sql .= 'INTO ' . $this->connection->quoteClause($this->table);

        // Add the columns
        if (!empty($this->columns)) {
            $sql .= ' (' . implode(',', array_map([$this->connection, 'quoteClause'], array_unique($this->columns))) . ')';
        }

        // Add the values
        if (!empty($this->parameters)) {
            $sql .=
                ' VALUES (' .
                implode('), (', array_fill(0, count($this->parameters) / count($this->columns), implode(',', array_fill(0, count($this->columns), '?')))) .
                ')';
        }

        return $sql;
    }
}