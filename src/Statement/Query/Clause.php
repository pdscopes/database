<?php

namespace MadeSimple\Database\Statement\Query;

use MadeSimple\Database\Connection;

/**
 * Class Clause
 *
 * @package MadeSimple\Database\Statement\Query
 * @author  Peter Scopes
 */
class Clause
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var array
     */
    protected $subClauses = [];

    /**
     * @param string $column
     * @param array $values
     *
     * @return string
     */
    public static function inX($column, $values)
    {
        return $column . ' IN (' . implode(' , ', array_fill(0, count($values), '?')) . ')';
    }

    /**
     * @param string $column
     * @param array $values
     *
     * @return string
     */
    public static function notInX($column, $values)
    {
        return $column . ' NOT IN (' . implode(' , ', array_fill(0, count($values), '?')) . ')';
    }

    /**
     * Clause constructor.
     *
     * @param Connection  $connection
     * @param null|string $clause
     */
    public function __construct(Connection $connection, $clause = null)
    {
        $this->connection = $connection;

        if (null !== $clause) {
            $this->subClauses[] = [null, $clause];
        }
    }

    /**
     * @param string|\Closure $subClause
     *
     * @return $this
     */
    public function where($subClause)
    {
        $this->subClauses = [[null, $subClause]];

        return $this;
    }

    /**
     * @param string|\Closure $subClause
     *
     * @return $this
     */
    public function andX($subClause)
    {
        if (empty($this->subClauses)) {
            $this->subClauses[] = [null, $subClause];
        } else {
            $this->subClauses[] = ['AND', $subClause];
        }

        return $this;
    }

    /**
     * @param string|\Closure $subClause
     *
     * @return $this
     */
    public function orX($subClause)
    {
        if (empty($this->subClauses)) {
            $this->subClauses[] = [null, $subClause];
        } else {
            $this->subClauses[] = ['OR', $subClause];
        }

        return $this;
    }

    /**
     * Flatten $this Clause and quote using $this connection.
     *
     * @return string
     */
    public function flatten()
    {
        return $this->connection->quoteClause($this->reduce());
    }

    /**
     * Reduce the complex Clause into a string.
     *
     * @return string
     */
    protected function reduce()
    {
        return array_reduce($this->subClauses, function ($carry, $item) {
            list ($conjunction, $subClause) = $item;
            if ($subClause instanceof \Closure) {
                $subClause = $subClause(new Clause($this->connection));
            }
            if ($subClause instanceof Clause) {
                $subClause = '(' . $subClause->reduce() . ')';
            }
            $subClause = ($conjunction ? ' ' . $conjunction . ' ' : '') . $subClause;

            return $carry . $subClause;
        }, '');
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->subClauses);
    }

    /**
     * @return string
     */
    function __toString()
    {
        return $this->flatten();
    }
}