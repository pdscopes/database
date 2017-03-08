<?php

namespace MadeSimple\Database\Statement\Query;

use MadeSimple\Database\Connection;
use MadeSimple\Database\Statement\Query;

/**
 * Class Select
 *
 * @package MadeSimple\Database\Statement\Query
 * @author  Peter Scopes
 */
class Select extends Query
{
    use WhereTrait;

    const JOIN_DEFAULT = 'JOIN';
    const JOIN_LEFT    = 'LEFT JOIN';
    const JOIN_RIGHT   = 'RIGHT JOIN';
    const JOIN_INNER   = 'INNER JOIN';
    const JOIN_FULL    = 'FULL JOIN';

    /**
     * @var string[]
     */
    protected $columns;

    /**
     * @var string[]
     */
    protected $from;

    /**
     * @var string[][]
     */
    protected $join;

    /**
     * @var string[]
     */
    protected $group;

    /**
     * @var string[]
     */
    protected $order;

    /**
     * @var string
     */
    protected $limit;

    /**
     * Select constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        $this->columns = ['*'];
        $this->from    = [];
        $this->join    = [];
        $this->where   = new Clause($connection);
        $this->group   = [];
        $this->order   = [];
        $this->limit   = '';
    }

    /**
     * Set the columns to select on.
     *
     * @param array ...$columns
     *
     * @return static
     */
    public function columns(... $columns)
    {
        $this->columns = [];

        return $this->addColumns($columns);
    }

    /**
     * Added columns to select on.
     *
     * @param array ...$columns
     *
     * @return static
     */
    public function addColumns(... $columns)
    {
        array_walk_recursive($columns, function ($e) { $this->columns[] = $e; });

        return $this;
    }

    /**
     * Set the table to select from.
     *
     * @param string      $table Database table to select from
     * @param null|string $alias Alias for the table
     *
     * @return static
     */
    public function from($table, $alias = null)
    {
        // Ensure alias has a value
        $alias = $alias ?: $table;

        $this->from = [$alias => $table];

        return $this;
    }

    /**
     * Add a table to select from.
     *
     * @param string      $table Database table to select from
     * @param null|string $alias Alias for the table
     *
     * @return static
     */
    public function addFrom($table, $alias = null)
    {
        // Ensure alias has a value
        $alias = $alias ?: $table;

        // Add the entry
        $this->from[$alias] = $table;

        return $this;
    }

    /**
     * @param string      $table Database table to join
     * @param string      $on    Clause to join the table on
     * @param null|string $alias Alias for the table
     *
     * @return static
     */
    public function leftJoin($table, $on, $alias = null)
    {
        return $this->join($table, $on, $alias, self::JOIN_LEFT);
    }

    /**
     * @param string      $table Database table to join
     * @param string      $on    Clause to join the table on
     * @param null|string $alias Alias for the table
     * @param string      $type  Select::JOIN_*
     *
     * @see self::JOIN_DEFAULT
     * @see self::JOIN_LEFT
     * @see self::JOIN_RIGHT
     * @see self::JOIN_INNER
     * @see self::JOIN_FULL
     * @see columns()
     *
     * @return static
     */
    public function join($table, $on, $alias = null, $type = self::JOIN_DEFAULT)
    {
        // Ensure alias has a value
        $alias = $alias ?: $table;

        // Add the entry
        $this->join[$alias] = [
            'table' => $table,
            'on'    => $on,
            'type'  => $type,
        ];

        return $this;
    }

    /**
     * @param string      $table Database table to join
     * @param string      $on    Clause to join the table on
     * @param null|string $alias Alias for the table
     *
     * @return static
     */
    public function rightJoin($table, $on, $alias = null)
    {
        return $this->join($table, $on, $alias, self::JOIN_RIGHT);
    }

    /**
     * @param string      $table Database table to join
     * @param string      $on    Clause to join the table on
     * @param null|string $alias Alias for the table
     *
     * @return static
     */
    public function fullJoin($table, $on, $alias = null)
    {
        return $this->join($table, $on, $alias, self::JOIN_FULL);
    }

    /**
     * @param string      $table Database table to join
     * @param string      $on    Clause to join the table on
     * @param null|string $alias Alias for the table
     *
     * @return static
     */
    public function innerJoin($table, $on, $alias = null)
    {
        return $this->join($table, $on, $alias, self::JOIN_INNER);
    }

    /**
     * Set the group by clauses to this select query.
     *
     * @param mixed $clauses Clauses to group by
     *
     * @return static
     */
    public function groupBy(... $clauses)
    {
        $this->group = [];

        return $this->addGroupBy($clauses);
    }

    /**
     * Adds group by clauses to this select query.
     *
     * @param mixed $clauses Clauses to group by
     *
     * @return static
     */
    public function addGroupBy(... $clauses)
    {
        array_walk_recursive($clauses, function ($e) { $this->group[] = $e; });

        return $this;
    }

    /**
     * Set the order by clauses to this select query.
     *
     * @param mixed $clauses Clauses to order by
     *
     * @return static
     */
    public function orderBy(... $clauses)
    {
        $this->order = [];

        return $this->addOrderBy($clauses);
    }

    /**
     * Adds order by clauses to this select query.
     *
     * @param mixed $clauses Clauses to order by
     *
     * @return static
     */
    public function addOrderBy(... $clauses)
    {
        array_walk_recursive($clauses, function ($e) { $this->order[] = $e; });

        return $this;
    }

    /**
     * Sets the limit for this select query.
     *
     * @param int      $range The range of rows to be returned
     * @param null|int $start The starting row of the returned rows
     *
     * @return static
     */
    public function limit($range, $start = null)
    {
        $this->limit = null === $start ? $range : $start . ', ' . $range;

        return $this;
    }

    /**
     * Convert the select object into the SQL.
     *
     * @return string
     */
    public function toSql()
    {
        $sql = 'SELECT ';

        // Add the select columns
        $sql .= implode(',', array_map([$this->connection, 'quoteColumn'], array_unique($this->columns)));

        // Add the from tables
        $tables = [];
        foreach ($this->from as $alias => $table) {
            $tables[] = $alias == $table ?
                $this->connection->quoteColumn($table) :
                $this->connection->quoteColumn($table) . ' AS ' . $this->connection->quoteColumn($alias);
        }
        $tables = array_unique($tables);
        $sql   .= ' FROM ' . implode(',', array_unique($tables));

        // If joins
        foreach ($this->join as $alias => $j) {
            $sql .=
                ' ' . $j['type'] . ' ' . $this->connection->quoteColumn($j['table']) .
                ($alias == $j['table'] ? '' : ' AS ' . $this->connection->quoteColumn($alias)) . ' ON ' .
                $this->connection->quoteClause($j['on']);
        }

        // If where
        if (!$this->where->isEmpty()) {
            $sql .= ' '.'WHERE ' . $this->where->flatten();
        }

        // If group
        if (!empty($this->group)) {
            $sql .= ' '.'GROUP BY ' . implode(',', array_map([$this->connection, 'quoteColumn'], $this->group));
        }

        // If order
        if (!empty($this->order)) {
            $sql .= ' '.'ORDER BY ' .
                implode(',', array_map(function ($e) {
                    list ($c, $d) =  explode(' ', $e.' ', 2);
                    return $this->connection->quoteColumn($c) . (empty($d) ? '' : ' '.trim(strtoupper($d)));
                }, $this->order));
        }

        // If limit
        if (!empty($this->limit)) {
            $sql .= ' '.'LIMIT ' . $this->limit;
        }

        return $sql;
    }
}