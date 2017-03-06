<?php

namespace MadeSimple\Database\QueryBuilder;

/**
 * Class Clause
 *
 * @package MadeSimple\Database\QueryBuilder
 * @author  Peter Scopes
 */
class Clause
{
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
     * Clause constructor.
     *
     * @param null|string $clause
     */
    public function __construct($clause = null)
    {
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
     * @return string
     */
    public function flatten()
    {
        return array_reduce($this->subClauses, function ($carry, $item) {
            list ($conjunction, $subClause) = $item;
            if ($subClause instanceof \Closure) {
                $subClause = $subClause(new Clause());
            }
            if ($subClause instanceof Clause) {
                $subClause = '(' . $subClause . ')';
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