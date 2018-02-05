<?php

namespace MadeSimple\Database\Query;

trait WhereTrait
{
    protected $statement = [];

    /**
     * Add a WHERE clause to the query.
     * This allows us to rename other functions as they are used.
     * For example, see JoinBuilder.
     * @see JoinBuilder
     *
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     * @param string $boolean
     *
     * @return static
     */
    protected function addWhere($column, $operator = null, $value = null, $boolean = 'and')
    {
        // If the value is a sub query
        if (is_callable($value)) {
            $callable = $value;
            $value    = new Select($this->connection, $this->logger);
            call_user_func($callable, $value);
        }

        // If the value is null and the operator is '=', '!=', or '<>'
        if ($value === null && in_array($operator, ['=', '!=', '<>'])) {
            $value = Raw::create('NULL');
            switch ($operator) {
                case '=':
                    $operator = 'is';
                    break;
                default:
                    $operator = 'is not';
                    break;
            }
        }

        $this->statement['where'][] = compact('column', 'operator', 'value', 'boolean');

        return $this;
    }

    /**
     * Add a WHERE clause to the query.
     *
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     * @param string $boolean
     *
     * @return static
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        return $this->addWhere($column, $operator, $value, $boolean);
    }

    /**
     * Add an OR WHERE clause to the query.
     *
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @return static
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->addWhere($column, $operator, $value, 'or');
    }

    /**
     * Add a WHERE clause with a Raw value to the query.
     *
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     * @param string $boolean
     *
     * @return static
     */
    public function whereRaw($column, $operator = null, $value = null, $boolean = 'and')
    {
        return $this->addWhere($column, $operator, Raw::create($value), $boolean);
    }

    /**
     * Add an OR WHERE clause with a Raw value to the query.
     *
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @return static
     */
    public function orWhereRaw($column, $operator = null, $value = null)
    {
        return $this->orWhere($column, $operator, Raw::create($value));
    }

    /**
     * Add a WHERE clause with the value being another column.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param string $boolean
     *
     * @return static
     */
    public function whereColumn($column, $operator = null, $value = null, $boolean = 'and')
    {
        return $this->addWhere($column, $operator, Column::create($value), $boolean);
    }

    /**
     * Add an OR WHERE clause with the value being another column.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     *
     * @return static
     */
    public function orWhereColumn($column, $operator = null, $value = null)
    {
        return $this->orWhere($column, $operator, Column::create($value));
    }

    /**
     * Add a WHERE EXISTS clause with $closure defining a select sub query.
     *
     * @param callable|Select $select  function (Select) {...}
     * @param string          $boolean
     * @param bool            $not
     *
     * @return static
     */
    public function whereExists($select, $boolean = 'and', $not = false)
    {
        if (is_callable($select)) {
            $callable = $select;
            $select   = new Select($this->connection, $this->logger);
            call_user_func($callable, $select);
        }

        $type   = 'exists';
        $select = $select->statement;

        $this->statement['where'][] = compact('type', 'select', 'boolean', 'not');

        return $this;
    }

    /**
     * Add a WHERE NOT EXISTS clause with $closure defining a select sub query.
     *
     * @param callable|Select $select  function (Select) {...}
     * @param string          $boolean
     *
     * @return static
     */
    public function whereNotExists($select, $boolean = 'and')
    {
        return $this->whereExists($select, $boolean, true);
    }

    /**
     * Add a WHERE (<sub query>) clause with $select defining a select sub query.
     *
     * @param callable|Select $select function (Select) {...}
     * @param string          $boolean
     *
     * @return static
     */
    public function whereSubQuery($select, $boolean = 'and')
    {
        if (is_callable($select)) {
            $callable = $select;
            $select   = new Select($this->connection, $this->logger);
            call_user_func($callable, $select);
        }

        $type = 'subQuery';
        $select = $select->statement;

        $this->statement['where'][] = compact('type','select', 'boolean');

        return $this;
    }

    /**
     * Add an OR WHERE (<sub query>) clause with $select defining a select sub query.
     *
     * @param callable|Select $select function (Select) {...}
     *
     * @return static
     */
    public function orWhereSubQuery($select)
    {
        return $this->whereSubQuery($select, 'or');
    }
}