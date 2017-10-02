<?php

namespace MadeSimple\Database\Query;

class Select extends QueryBuilder
{
    use WhereTrait;

    /**
     * Set the columns to select on.
     * Providing an associative array will use the key as AS.
     *
     * @param string|array|... $columns
     *
     * @return Select
     */
    public function columns($columns)
    {
        unset($this->statement['columns']);

        return $this->addToStatement('columns', is_array($columns) ? $columns : func_get_args());
    }

    /**
     * Add columns to select on.
     *
     * @param string|array|... $columns
     *
     * @return Select
     */
    public function addColumns($columns)
    {
        return $this->addToStatement('columns', is_array($columns) ? $columns : func_get_args());
    }

    /**
     * Set the table to select from.
     *
     * @param string      $table
     * @param null|string $alias
     *
     * @return Select
     */
    public function from($table, $alias = null)
    {
        unset($this->statement['from']);

        return $this->addToStatement('from', null === $alias ? [$table] : [$alias => $table]);
    }

    /**
     * Add a table to select from.
     *
     * @param string      $table
     * @param null|string $alias
     *
     * @return Select
     */
    public function addFrom($table, $alias = null)
    {
        return $this->addToStatement('from', null === $alias ? [$table] : [$alias => $table]);
    }

    /**
     * Add a join to this select query.
     *
     * @param string          $table
     * @param string|\Closure $column1
     * @param null|string     $operator
     * @param null|mixed      $column2
     * @param null|mixed      $alias
     * @param string          $type     inner|left|right
     *
     * @return Select
     */
    public function join($table, $column1, $operator = null, $column2 = null, $alias = null, $type = 'inner')
    {
        if (!$column1 instanceof \Closure) {
            $column1 = function (JoinBuilder $joinBuilder) use ($column1, $operator, $column2) {
                $joinBuilder->on($column1, $operator, $column2);
            };
        }

        $joinBuilder = (new JoinBuilder($this->connection));
        $column1($joinBuilder);
        $statement = $joinBuilder->statement;

        $this->statement['join'][] = compact('type', 'table', 'alias', 'statement');

        return $this;
    }

    /**
     * Add a left join to this select query.
     *
     * @param string          $table
     * @param string|\Closure $column1
     * @param null|string     $operator
     * @param null|mixed      $column2
     * @param null|mixed      $alias
     *
     * @return Select
     */
    public function leftJoin($table, $column1, $operator = null, $column2 = null, $alias = null)
    {
        return $this->join($table, $column1, $operator, $column2, $alias, 'left');
    }

    /**
     * Add a right join to this select query.
     *
     * @param string          $table
     * @param string|\Closure $column1
     * @param null|string     $operator
     * @param null|mixed      $column2
     * @param null|mixed      $alias
     *
     * @return Select
     */
    public function rightJoin($table, $column1, $operator = null, $column2 = null, $alias = null)
    {
        return $this->join($table, $column1, $operator, $column2, $alias, 'right');
    }

    /**
     * Add a set of group by columns to this select query.
     *
     * @param string|array|... $columns
     *
     * @return Select
     */
    public function groupBy($columns)
    {
        return $this->addToStatement('groupBy', is_array($columns) ? $columns : func_get_args());
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     * @param string $boolean
     *
     * @return Select
     */
    public function having($column, $operator, $value, $boolean = 'and')
    {
        $this->statement['having'][] = compact('column', 'operator', 'value', 'boolean');

        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @return Select
     */
    public function orHaving($column, $operator, $value)
    {
        return $this->having($column, $operator, $value, 'or');
    }

    /**
     * Add an order by columns to this select query.
     *
     * @param string $column
     * @param string $direction asc|desc
     *
     * @return Select
     */
    public function orderBy($column, $direction = 'asc')
    {
        $this->statement['orderBy'][] = compact('column', 'direction');

        return $this;
    }

    /**
     * Set the limit for this select query.
     *
     * @param int $limit
     *
     * @return Select
     */
    public function limit($limit)
    {
        $this->statement['limit'] = $limit;

        return $this;
    }

    /**
     * Set the offset for this select query.
     *
     * @param int $offset
     *
     * @return Select
     */
    public function offset($offset)
    {
        $this->statement['offset'] = $offset;

        return $this;
    }


    /**
     * Count the matching rows.
     *
     * @return int
     */
    public function count()
    {
        $statement = $this->statement;

        unset($statement['orderBy']);
        unset($statement['limit']);
        unset($statement['offset']);
        $statement['columns'] = [new Raw('COUNT(*)')];

        list($sql, $bindings) = $this->buildSql($statement);
        /** @var \PDOStatement $pdoStatement */
        list($pdoStatement) = $this->statement($sql, $bindings);

        return $pdoStatement->fetch(\PDO::FETCH_COLUMN) ?? 0;
    }

    /**
     * Calls PDOStatement::fetch()
     *
     * @param null|... $parameters
     * @see \PDOStatement::fetch()
     *
     * @return mixed
     */
    public function fetch($parameters = null)
    {
        if ($this->pdoStatement === null) {
            $this->query();
        }
        $parameters = null === $parameters ? $this->fetchMode : func_get_args();
        call_user_func_array([$this->pdoStatement, 'setFetchMode'], $parameters);
        return $this->pdoStatement->fetch();
    }

    /**
     * Calls PDOStatement::fetchAll()
     *
     * @param null|... $parameters
     * @see \PDOStatement::fetchAll()
     *
     * @return mixed
     */
    public function fetchAll($parameters = null)
    {
        if ($this->pdoStatement === null) {
            $this->query();
        }
        $parameters = null === $parameters ? $this->fetchMode : func_get_args();
        call_user_func_array([$this->pdoStatement, 'setFetchMode'], $parameters);
        return $this->pdoStatement->fetchAll();
    }


    public  function buildSql(array $statement = null)
    {
        if (null === $statement) {
            $statement = $this->statement;
        }

        return $this->compiler->compileQuerySelect($statement);
    }
}