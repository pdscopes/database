<?php

namespace MadeSimple\Database;

use MadeSimple\Database\Query;

abstract class  Relationship
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var string
     */
    protected $relation;

    /**
     * @var string
     */
    protected $intermediateTable;

    /**
     * @var string[]
     */
    protected $intermediateColumns;

    /**
     * @var string
     */
    protected $intermediateAlias;

    /**
     * @var Query\Select
     */
    protected $query;

    /**
     * Relationship constructor.
     *
     * @param Entity $entity
     */
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Get the result of the relationship. This could be a single entity or an array of entities
     *
     * @return mixed
     */
    public abstract function fetch();

    /**
     * @param string       $related
     * @param string       $relatedAlias
     * @param string|array $entityColumns
     * @param string|array $relatedColumns
     *
     * @return static
     */
    public function belongsTo($related, $relatedAlias, $entityColumns, $relatedColumns = null)
    {
        /** @var Entity $relatedEntity */
        $relatedEntity  = new $related();
        $relatedMap     = $relatedEntity->getMap();
        $entityMap      = $this->entity->getMap();
        $relatedTable   = $relatedMap->tableName();
        $relatedAlias   = $relatedAlias ?? $relatedTable;
        $entityColumns  = (array) $entityColumns;
        $relatedColumns = $relatedColumns ? (array) $relatedColumns :  $relatedMap->columns();

        if (null === $this->query) {
            $this->query = $this->entity->pool->get($relatedEntity::$connection)->select();
            $this->query->columns($relatedAlias . '.*')->from($relatedTable, $relatedAlias);

            // Construct the where clause(s)
            foreach ($entityColumns as $idx => $column) {
                $relatedColumn = $relatedColumns[$idx];
                $entityValue   = $this->entity->{$entityMap->property($column)};
                $this->query->where($relatedAlias . '.' . $relatedColumn, '=', $entityValue);
            }
        } else {
            $intermediateAlias = $this->intermediateAlias;

            $this->query
                ->columns($relatedAlias . '.*')
                ->from($relatedTable, $relatedAlias)
                ->join($this->intermediateTable, function ($join) use ($intermediateAlias, $entityColumns, $relatedAlias, $relatedColumns) {
                    foreach ($entityColumns as $idx => $column) {
                        /** @var Query\JoinBuilder $join */
                        $join->where($intermediateAlias . '.' . $column, ' = ', $relatedAlias . '.' . $relatedColumns[$idx]);
                    }
                }, null, null, $this->intermediateAlias);
        }

        // Store information about the relation as it may become an intermediate table
        $this->relation            = $related;
        $this->intermediateTable   = $relatedTable;
        $this->intermediateColumns = $relatedMap->columns();
        $this->intermediateAlias   = $relatedAlias;

        return $this;
    }

    /**
     * @param string       $related
     * @param string       $relatedAlias
     * @param string|array $relatedColumns
     * @param string|array $entityColumns
     *
     * @return static
     */
    public function has($related, $relatedAlias, $relatedColumns, $entityColumns = null)
    {
        /** @var Entity $relatedEntity */
        $relatedEntity  = new $related();
        $relatedMap     = $relatedEntity->getMap();
        $entityMap      = $this->entity->getMap();
        $relatedTable   = $relatedMap->tableName();
        $relatedAlias   = $relatedAlias ?? $relatedTable;
        $entityColumns  = $entityColumns ? (array) $entityColumns : $entityMap->columns();
        $relatedColumns = (array) $relatedColumns;

        if (null === $this->query) {
            $this->query = $this->entity->pool->get($relatedEntity::$connection)->select();
            $this->query->columns($relatedAlias . '.*')->from($relatedTable, $relatedAlias);

            // Construct the where clause(s)
            foreach ($relatedColumns as $idx => $column) {
                $entityValue = $this->entity->{$entityMap->property($entityColumns[$idx])};
                $this->query->where($relatedAlias . '.' . $column, '=',$entityValue);
            }
        } else {
            $intermediateAlias = $this->intermediateAlias;

            $this->query
                ->columns($relatedAlias . '.*')
                ->from($relatedTable, $relatedAlias)
                ->join($this->intermediateTable, function ($join) use ($intermediateAlias, $entityColumns, $relatedAlias, $relatedColumns) {
                    foreach ($relatedColumns as $idx => $column) {
                        /** @var Query\JoinBuilder $join */
                        $join->where($intermediateAlias . '.' . $entityColumns[$idx], '=', $relatedAlias . '.' . $column);
                    }
                }, null, null, $this->intermediateAlias);
        }

        // Store information about the relation as it may become an intermediate table
        $this->relation            = $related;
        $this->intermediateTable   = $relatedTable;
        $this->intermediateColumns = $relatedMap->columns();
        $this->intermediateAlias   = $relatedAlias;

        return $this;
    }

    /**
     * @return Query\Select
     */
    public function query()
    {
        return clone $this->query;
    }

    /**
     * @see Query\Select::toSql()
     * @return string
     */
    public function toSql()
    {
        return $this->query->toSql();
    }

    /**
     * @param string|array|... $columns
     *
     * @see \MadeSimple\Database\Query\Select::columns()
     * @return static
     */
    public function columns($columns)
    {
        call_user_func_array([$this->query, 'columns'], func_get_args());
        return $this;
    }

    /**
     * @param string|array|... $columns
     *
     * @see   \MadeSimple\Database\Query\Select::addColumns()
     * @return static
     */
    public function addColumns($columns)
    {
        call_user_func_array([$this->query, 'addColumns'], func_get_args());
        return $this;
    }

    /**
     * @param string          $table
     * @param string|\Closure $column1
     * @param null|string     $operator
     * @param null|mixed      $column2
     * @param null|mixed      $alias
     * @param string          $type     inner|left|right
     *
     * @see \MadeSimple\Database\Query\Select::join()
     * @return static
     */
    public function join($table, $column1, $operator = null, $column2 = null, $alias = null, $type = 'inner')
    {
        call_user_func_array([$this->query, 'join'], func_get_args());
        return $this;
    }

    /**
     * @param string          $table
     * @param string|\Closure $column1
     * @param null|string     $operator
     * @param null|mixed      $column2
     * @param null|mixed      $alias
     *
     * @see \MadeSimple\Database\Query\Select::leftJoin()
     * @return static
     */
    public function leftJoin($table, $column1, $operator = null, $column2 = null, $alias = null)
    {
        call_user_func_array([$this->query, 'leftJoin'], func_get_args());
        return $this;
    }

    /**
     * @param string          $table
     * @param string|\Closure $column1
     * @param null|string     $operator
     * @param null|mixed      $column2
     * @param null|mixed      $alias
     *
     * @see \MadeSimple\Database\Query\Select::rightJoin()
     * @return static
     */
    public function rightJoin($table, $column1, $operator = null, $column2 = null, $alias = null)
    {
        call_user_func_array([$this->query, 'rightJoin'], func_get_args());
        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     * @param string $boolean
     *
     * @see \MadeSimple\Database\Query\Select::where()
     * @return static
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        call_user_func_array([$this->query, 'where'], func_get_args());
        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @see \MadeSimple\Database\Query\Select::orWhere()
     * @return static
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        call_user_func_array([$this->query, 'orWhere'], func_get_args());
        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     * @param string $boolean
     *
     * @see \MadeSimple\Database\Query\Select::whereRaw()
     * @return static
     */
    public function whereRaw($column, $operator = null, $value = null, $boolean = 'and')
    {
        call_user_func_array([$this->query, 'whereRaw'], func_get_args());
        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @see \MadeSimple\Database\Query\Select::orWhereRaw()
     * @return static
     */
    public function orWhereRaw($column, $operator = null, $value = null)
    {
        call_user_func_array([$this->query, 'orWhereRaw'], func_get_args());
        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     * @param string $boolean
     *
     * @see \MadeSimple\Database\Query\Select::whereColumn()
     * @return static
     */
    public function whereColumn($column, $operator = null, $value = null, $boolean = 'and')
    {
        call_user_func_array([$this->query, 'whereColumn'], func_get_args());
        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @see \MadeSimple\Database\Query\Select::orWhereColumn()
     * @return static
     */
    public function orWhereColumn($column, $operator = null, $value = null)
    {
        call_user_func_array([$this->query, 'orWhereColumn'], func_get_args());
        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     * @param string $boolean
     *
     * @see \MadeSimple\Database\Query\Select::whereExists()
     * @return static
     */
    public function whereExists($column, $operator = null, $value = null, $boolean = 'and')
    {
        call_user_func_array([$this->query, 'whereExists'], func_get_args());
        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @see \MadeSimple\Database\Query\Select::whereNotExists()
     * @return static
     */
    public function whereNotExists($column, $operator = null, $value = null)
    {
        call_user_func_array([$this->query, 'whereNotExists'], func_get_args());
        return $this;
    }

    /**
     * @param string|array|... $columns
     * @see \MadeSimple\Database\Query\Select::groupBy()
     * @return static
     */
    public function groupBy($columns)
    {
        call_user_func_array([$this->query, 'groupBy'], func_get_args());
        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     * @param string $boolean
     *
     * @see \MadeSimple\Database\Query\Select::having()
     * @return static
     */
    public function having($column, $operator, $value, $boolean = 'and')
    {
        call_user_func_array([$this->query, 'having'], func_get_args());
        return $this;
    }

    /**
     * @param string $column
     * @param string $operator
     * @param mixed  $value
     *
     * @see \MadeSimple\Database\Query\Select::orHaving()
     * @return static
     */
    public function orHaving($column, $operator, $value)
    {
        call_user_func_array([$this->query, 'orHaving'], func_get_args());

        return $this;
    }

    /**
     * @param string $column
     * @param string $direction asc|desc
     * @see \MadeSimple\Database\Query\Select::groupBy()
     * @return static
     */
    public function orderBy($column, $direction = 'asc')
    {
        call_user_func_array([$this->query, 'orderBy'], func_get_args());
        return $this;
    }
}