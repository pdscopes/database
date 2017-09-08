<?php

namespace MadeSimple\Database\Query;

trait WhereTrait
{
    protected $statement;

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
        return $this->addWhere($column, $operator, new Raw($value), $boolean);
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
        return $this->orWhere($column, $operator, new Raw($value));
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
        return $this->addWhere($column, $operator, new Column($value), $boolean);
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
        return $this->orWhere($column, $operator, new Column($value));
    }

    /**
     * Add a WHERE EXISTS clause with $closure defining a select sub query.
     *
     * @param \Closure $closure function (Select) {...}
     * @param string   $boolean
     * @param bool     $not
     *
     * @return static
     */
    public function whereExists(\Closure $closure, $boolean = 'and', $not = false)
    {
        $type    = 'exists';
        $builder = new Select($this->connection);
        call_user_func($closure, $builder);

        $this->statement['where'][] = compact('type', 'builder', 'boolean', 'not');

        return $this;
    }

    /**
     * Add a WHERE NOT EXISTS clause with $closure defining a select sub query.
     *
     * @param \Closure $closure function (Select) {...}
     * @param string   $boolean
     *
     * @return static
     */
    public function whereNotExists(\Closure $closure, $boolean = 'and')
    {
        return $this->whereExists($closure, $boolean, true);
    }
}