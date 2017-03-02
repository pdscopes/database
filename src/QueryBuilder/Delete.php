<?php

namespace MadeSimple\Database\QueryBuilder;

use MadeSimple\Database\Connection;

/**
 * Class Delete
 *
 * @package MadeSimple\Database\QueryBuilder
 * @author  Peter Scopes
 */
class Delete extends Statement
{
    /**
     * @var string[]
     */
    protected $table;

    /**
     * @var Clause
     */
    protected $where;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * Delete constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        $this->table      = [];
        $this->where      = new Clause();
        $this->parameters = [];
    }


    /**
     * @param string      $table Database table name
     * @param null|string $alias Alias for the table name
     *
     * @return Delete
     */
    public function from($table, $alias = null)
    {
        $this->table = [$alias ?: $table, $table];

        return $this;
    }

    /**
     * @param null|string $name  Name of the parameter (used in select query)
     * @param mixed       $value Value of the parameter (must be convertible to string)
     *
     * @return Delete
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
     * @return Delete
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
     * @return Delete
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
     * @return Delete
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
     * @return Delete
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

        return $statement;
    }

    /**
     * @return string
     */
    public function toSql()
    {
        $sql = 'DELETE ';

        // Add the from tables
        $alias = $this->connection->quoteColumn($this->table[0]);
        $table = $this->connection->quoteColumn($this->table[1]);
        $sql .= $alias == $table ? 'FROM ' . $table : $alias . ' FROM ' . $table . ' AS ' . $alias;

        // Add possible where clause(s)
        if (!$this->where->isEmpty()) {
            $sql .= "\nWHERE " . $this->connection->quoteClause($this->where->flatten());
        }

        return $sql;
    }
}