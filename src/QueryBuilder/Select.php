<?php

namespace MadeSimple\Database\QueryBuilder;

use MadeSimple\Database\Connection;
use PDO;

/**
 * Class Select
 *
 * @package MadeSimple\Database\QueryBuilder
 * @author  Peter Scopes
 */
class Select extends Statement
{
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
    protected $where;

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
     * @var array
     */
    protected $parameters;

    /**
     * Select constructor.
     *
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo);

        $this->columns = ['*'];
        $this->from    = [];
        $this->join    = [];
        $this->where   = [];

        $this->group = [];
        $this->order = [];
        $this->limit = '';

        $this->parameters = [];
    }

    /**
     * Set the columns to select on.
     *
     * @param array ...$columns
     *
     * @return $this
     */
    public function columns(... $columns)
    {
        $this->columns = array_reduce($columns, function ($carry, $item) {
            return array_merge($carry, is_array($item) ? $item : [$item]);
        }, []
        );

        return $this;
    }

    /**
     * Added columns to select on.
     *
     * @param array ...$columns
     *
     * @return $this
     */
    public function addColumns(... $columns)
    {
        $this->columns = array_reduce($columns, function ($carry, $item) {
            return array_merge($carry, is_array($item) ? $item : [$item]);
        }, $this->columns
        );

        return $this;

    }

    /**
     * Set the table to select from.
     *
     * @param string      $table Database table to select from
     * @param null|string $alias Alias for the table
     *
     * @return Select
     */
    public function from($table, $alias = null)
    {
        // Ensure alias has a value
        $alias = !is_null($alias) ? $alias : $table;

        $this->from = [$alias => $table];

        return $this;
    }

    /**
     * Add a table to select from.
     *
     * @param string      $table Database table to select from
     * @param null|string $alias Alias for the table
     *
     * @return $this
     */
    public function addFrom($table, $alias = null)
    {
        // Ensure alias has a value
        $alias = !is_null($alias) ? $alias : $table;

        // Add the entry
        $this->from[$alias] = $table;

        return $this;
    }

    /**
     * @param string      $table Database table to join
     * @param string      $on    Clause to join the table on
     * @param null|string $alias Alias for the table
     *
     * @return Select
     */
    public function leftJoin($table, $on, $alias = null)
    {
        return $this->join($table, $on, $alias, self::JOIN_LEFT);
    }

    /**
     * @param string      $table Database table to join
     * @param string      $on    Clause to join the table on
     * @param null|string $alias Alias for the table
     * @param string      $type  JOIN_DEFAULT|JOIN_LEFT|JOIN_RIGHT|JOIN_INNER|JOIN_FULL
     *
     * @see self::JOIN_DEFAULT
     * @see self::JOIN_LEFT
     * @see self::JOIN_RIGHT
     * @see self::JOIN_INNER
     * @see self::JOIN_FULL
     * @see columns()
     *
     * @return Select
     */
    public function join($table, $on, $alias = null, $type = self::JOIN_DEFAULT)
    {
        // Ensure alias has a value
        $alias = !is_null($alias) ? $alias : $table;

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
     * @return Select
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
     * @return Select
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
     * @return Select
     */
    public function innerJoin($table, $on, $alias = null)
    {
        return $this->join($table, $on, $alias, self::JOIN_INNER);
    }

    /**
     * @param string      $clause    A where clause
     * @param array|mixed $parameter A single, array of, or associated mapping of parameters
     *
     * @return Select
     */
    public function where($clause, $parameter = null)
    {
        $this->where[] = $clause;
        if (!is_null($parameter)) {
            $this->setParameters(!is_array($parameter) ? [$parameter] : $parameter);
        }

        return $this;
    }

    /**
     * @param array $parameters Associated mapping of parameter name to value
     *
     * @return Select
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter(is_numeric($name) ? null : $name, $value);
        }

        return $this;
    }

    /**
     * @param null|string $name  Name of the parameter (used in select query)
     * @param mixed       $value Value of the parameter (must be convertible to string)
     *
     * @return Select
     */
    public function setParameter($name, $value)
    {
        if (null !== $name) {
            $this->parameters[$name] = $value;
        } else {
            $this->parameters[] = $value;
        }

        return $this;
    }

    /**
     * Given clauses are OR'd together.
     *
     * @param string[]    $clauses   Array of where clauses
     * @param array|mixed $parameter A single, array of, or associated mapping of parameters
     *
     * @return Select
     */
    public function orWhere(array $clauses, $parameter = null)
    {
        $this->where[] = sprintf('((%s))', implode(') OR (', $clauses));
        if (!is_null($parameter)) {
            $this->setParameters(!is_array($parameter) ? [$parameter] : $parameter);
        }

        return $this;
    }

    /**
     * e.g.:
     * ['AND', [
     *      ['OR', [
     *          'inner OR clause 1',
     *          'inner OR clause 2',
     *      ]],
     *      'outer AND clause'
     * ]]
     *
     * ((((inner OR clause 1) OR (inner OR clause 2))) AND (outer AND clause))
     *
     * @param string $conjunction
     * @param array  $tree
     *
     * @return Select
     */
    public function treeWhere($conjunction, array $tree)
    {
        $this->where[] = $this->collapseTree([$conjunction, $tree]);

        return $this;
    }

    /**
     * @param array $tree
     *
     * @return string
     */
    protected function collapseTree(array $tree)
    {
        list($conjunction, $clauses) = $tree;

        foreach ($clauses as $k => $clause) {
            if (is_array($clause) && in_array(reset($clause), ['OR', 'AND'])) {
                $clauses[$k] = $this->collapseTree($clause);
            }
        }

        return sprintf('((%s))', implode(sprintf(') %s (', $conjunction), $clauses));
    }

    /**
     * Set the group by clauses to this select query.
     *
     * @param mixed $clauses Clauses to group by
     *
     * @return Select
     */
    public function groupBy(... $clauses)
    {
        $this->group = array_reduce($clauses, function ($carry, $item) {
            return array_merge($carry, is_array($item) ? $item : [$item]);
        }, []
        );

        return $this;
    }

    /**
     * Adds group by clauses to this select query.
     *
     * @param mixed $clauses Clauses to group by
     *
     * @return Select
     */
    public function addGroupBy(... $clauses)
    {
        $this->group = array_reduce($clauses, function ($carry, $item) {
            return array_merge($carry, is_array($item) ? $item : [$item]);
        }, $this->group
        );

        return $this;
    }

    /**
     * Set the order by clauses to this select query.
     *
     * @param mixed $clauses Clauses to order by
     *
     * @return Select
     */
    public function orderBy(... $clauses)
    {
        $this->order = array_reduce($clauses, function ($carry, $item) {
            return array_merge($carry, is_array($item) ? $item : [$item]);
        }, []
        );

        return $this;
    }

    /**
     * Adds order by clauses to this select query.
     *
     * @param mixed $clauses Clauses to order by
     *
     * @return Select
     */
    public function addOrderBy(... $clauses)
    {
        $this->order = array_reduce($clauses, function ($carry, $item) {
            return array_merge($carry, is_array($item) ? $item : [$item]);
        }, $this->order
        );

        return $this;
    }

    /**
     * Sets the limit for this select query.
     *
     * @param int      $range The range of rows to be returned
     * @param null|int $start The starting row of the returned rows
     *
     * @return Select
     */
    public function limit($range, $start = null)
    {
        $this->limit = is_null($start) ?
            sprintf('%d', $range) :
            sprintf('%d, %d', $start, $range);

        return $this;
    }

    /**
     * {@InheritDoc}
     */
    public function execute(array $parameters = null)
    {
        $statement = null;
        if (empty($this->parameters) && empty($parameters)) {
            $statement = $this->pdo->query($this->toSql());
        } else {
            $statement = $this->pdo->prepare($this->toSql());
            $this->bindParameters($statement, $parameters ? : $this->parameters);
            if (false === $statement->execute()) {
                return false;
            }
        }

        return $statement;
    }

    /**
     * Convert the select object into the SQL.
     *
     * @return string
     */
    public function toSql()
    {
        // Add the select columns
        $sql =
            sprintf("SELECT %s", implode(',', array_map([Connection::class, 'quoteColumn'], array_unique($this->columns))));

        // Add the from tables
        $tables = [];
        foreach ($this->from as $alias => $table) {
            $tables[] = $alias == $table ?
                Connection::quoteColumn($table) :
                sprintf('%s AS %s', Connection::quoteColumn($table), Connection::quoteColumn($alias));
        }
        $tables = array_unique($tables);

        $sql .= sprintf("\nFROM %s", implode(',', array_unique($tables)));

        // If joins
        foreach ($this->join as $alias => $j) {
            $sql .= $alias == $j['table'] ?
                sprintf("\n%s %s ON %s", $j['type'], Connection::quoteColumn($alias), Connection::quoteClause($j['on'])) :
                sprintf("\n%s %s AS %s ON %s", $j['type'], Connection::quoteColumn($j['table']), Connection::quoteColumn($alias), Connection::quoteClause($j['on']));
        }

        // If where
        if (!empty($this->where)) {
            $sql .= sprintf("\nWHERE\n    %s", implode("\nAND ", array_map([Connection::class, 'quoteClause'], $this->where)));
        }

        // If group
        if (!empty($this->group)) {
            $sql .= sprintf("\nGROUP BY %s", implode(',', array_map([Connection::class, 'quoteColumn'], $this->group)));
        }

        // If order
        if (!empty($this->order)) {
            $sql .= sprintf("\nORDER BY %s", implode(',', array_map(function ($e) {
                        list ($column, $direction) = explode(' ', $e . ' ', 2);

                        return Connection::quoteColumn($column) . (empty($direction) ? '' : ' ' . trim($direction));
                    }, $this->order
                    )
                )
            );
        }

        // If limit
        if (!empty($this->limit)) {
            $sql .= sprintf("\nLIMIT %s", $this->limit);
        }

        return $sql;
    }
}