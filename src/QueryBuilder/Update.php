<?php

namespace MadeSimple\Database\QueryBuilder;

use MadeSimple\Database\Connection;

/**
 * Class Update
 *
 * @package MadeSimple\Database\QueryBuilder
 * @author  Peter Scopes
 */
class Update extends Statement
{
    /**
     * @var string[]
     */
    protected $table;

    /**
     * @var array
     */
    protected $columns;

    /**
     * @var string[]
     */
    protected $parameters;

    /**
     * @var Clause
     */
    protected $where;

    /**
     * Update constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        $this->columns    = [];
        $this->parameters = [];
        $this->where      = new Clause();
    }

    /**
     * @param string      $table Database table name
     * @param null|string $alias Alias for the table name
     *
     * @return Update
     */
    public function table($table, $alias = null)
    {
        $this->table = [$alias ?: $table, $table];

        return $this;
    }

    /**
     * @param string $column
     * @param mixed  $value
     *
     * @return Update
     */
    public function set($column, $value)
    {
        $this->columns[$column] = ':' . $column;
        $this->setParameter($column, $value);

        return $this;
    }

    /**
     * @param array ...$columns columns to be updated
     *
     * @return Update
     */
    public function columns(... $columns)
    {
        $this->columns = [];
        array_walk_recursive($columns, function ($v) { $this->columns[$v] = '?'; });


        return $this;
    }

    /**
     * @param array ...$values
     *
     * @return Update
     */
    public function values(... $values)
    {
        $flattened = [];
        array_walk_recursive($values, function ($v) use (&$flattened) { $flattened[] = $v; });

        return $this->setParameters($flattened);
    }

    /**
     * @param null|string $name  Name of the parameter (used in select query)
     * @param mixed       $value Value of the parameter (must be convertible to string)
     *
     * @return Update
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
     * @param array $parameters Associated mapping of parameter name to value
     *
     * @return Update
     */
    public function setParameters(array $parameters)
    {
        foreach ($parameters as $name => $value) {
            $this->setParameter(is_numeric($name) ? null : $name, $value);
        }

        return $this;
    }

    /**
     * @param string|\Closure $clause    A where clause or closure
     * @param array|mixed     $parameter A single, array of, or associated mapping of parameters
     *
     * @return Update
     */
    public function where($clause, $parameter = null)
    {
        $this->where->where($clause);
        if (null !== $parameter) {
            $this->setParameters(!is_array($parameter) ? [$parameter] : $parameter);
        }

        return $this;
    }

    /**
     * @param string|\Closure $clause    A where clause or closure
     * @param array|mixed     $parameter A single, array of, or associated mapping of parameters
     *
     * @return Update
     */
    public function andWhere($clause, $parameter = null)
    {
        $this->where->andX($clause);
        if (null !== $parameter) {
            $this->setParameters(!is_array($parameter) ? [$parameter] : $parameter);
        }

        return $this;
    }

    /**
     * @param string|\Closure $clause    A where clause or closure
     * @param array|mixed     $parameter A single, array of, or associated mapping of parameters
     *
     * @return Update
     */
    public function orWhere($clause, $parameter = null)
    {
        $this->where->orX($clause);
        if (null !== $parameter) {
            $this->setParameters(!is_array($parameter) ? [$parameter] : $parameter);
        }

        return $this;
    }

    /**
     * {@InheritDoc}
     * If successful, clears the parameters.
     */
    public function execute(array $parameters = null)
    {
        $statement = null;
        if (empty($this->parameters) && empty($parameters)) {
            $statement = $this->connection->query($this->toSql());
        } else {
            $statement = $this->connection->prepare($this->toSql());
            $this->bindParameters($statement, $parameters ? : $this->parameters);
            if (false === $statement->execute()) {
                return false;
            }
        }
        $this->parameters = [];

        return $statement;
    }

    /**
     * @return string
     */
    public function toSql()
    {
        $sql = 'UPDATE ';

        // Set the table
        $alias = $this->connection->quoteColumn($this->table[0]);
        $table = $this->connection->quoteColumn($this->table[1]);
        $sql .= $alias == $table ? $table : $table . ' AS ' . $alias;

        // Set the SET
        $sql .= "\nSET\n";
        $sql .= implode(",\n", array_map(function ($col, $val) {
            return $this->connection->quoteColumn($col) . ' = ' . $val;
        }, array_keys($this->columns), $this->columns));

        // Add possible where clause(s)
        if (!$this->where->isEmpty()) {
            $sql .= "\nWHERE " . $this->connection->quoteClause($this->where->flatten());
        }

        return $sql;
    }
}