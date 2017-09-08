<?php

namespace MadeSimple\Database\Query;

use MadeSimple\Database\Builder;

class JoinBuilder extends Builder
{
    use WhereTrait {
        where   as public whereParameter;
        orWhere as public orWhereParameter;
    }
    /**
     * @param string      $column
     * @param null|string $operator
     * @param null|string $value
     * @param string      $boolean
     *
     * @return JoinBuilder
     */
    public function on($column, $operator = null, $value = null, $boolean = 'and')
    {
        return $this->whereColumn($column, $operator, $value, $boolean);
    }

    /**
     * Where defaults to the value being a column for string values.
     *
     * @see JoinBuilder::whereParameter()
     *
     * @param string      $column
     * @param null|string $operator
     * @param null|string $value
     * @param string      $boolean
     *
     * @return JoinBuilder
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        return $this->whereColumn($column, $operator, $value, $boolean);
    }

    /**
     * Where defaults to the value being a column for string values.
     *
     * @see JoinBuilder::orWhereParameter()
     *
     * @param string      $column
     * @param null|string $operator
     * @param null|string $value
     *
     * @return JoinBuilder
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->orWhereColumn($column, $operator, $value);
    }
}