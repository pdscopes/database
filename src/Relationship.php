<?php

namespace MadeSimple\Database;

use MadeSimple\Database\Statement\Query;

/**
 * Class Relationship
 *
 * @package MadeSimple\Database
 * @author  Peter Scopes
 */
abstract class Relationship
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
        $relatedAlias   = $relatedAlias ? : $relatedTable;
        $entityColumns  = (array) $entityColumns;
        $relatedColumns = $relatedColumns ? (array) $relatedColumns :  $relatedMap->columns();

        if (null === $this->query) {
            $this->query = $this->entity->pool->get($related::$connection)->select();
            $this->query->columns($relatedAlias . '.*')->from($relatedTable, $relatedAlias);

            // Construct the where clause(s)
            foreach ($entityColumns as $idx => $column) {
                $relatedColumn = $relatedColumns[$idx];
                $entityValue   = $this->entity->{$entityMap->property($column)};
                $this->query->andWhere(
                    $relatedAlias . '.' . $relatedColumn . ' = :' . $relatedColumn,
                    [$relatedColumn => $entityValue]
                );
            }
        } else {
            // Construct join clause
            $clause = new Query\Clause($this->query->connection);
            foreach ($entityColumns as $idx => $column) {
                $clause->andX($this->intermediateAlias . '.' . $column . ' = ' . $relatedAlias . '.' . $relatedColumns[$idx]);
            }

            $this->query
                ->columns($relatedAlias . '.*')
                ->from($relatedTable, $relatedAlias)
                ->innerJoin($this->intermediateTable, $clause, $this->intermediateAlias);
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
        $relatedAlias   = $relatedAlias ? : $relatedTable;
        $entityColumns  = $entityColumns ? (array) $entityColumns : $entityMap->columns();
        $relatedColumns = (array) $relatedColumns;

        if (null === $this->query) {
            $this->query = $this->entity->pool->get($related::$connection)->select();
            $this->query->columns($relatedAlias . '.*')->from($relatedTable, $relatedAlias);

            // Construct the where clause(s)
            foreach ($relatedColumns as $idx => $column) {
                $entityValue = $this->entity->{$entityMap->property($entityColumns[$idx])};
                $this->query->andWhere($relatedAlias . '.' . $column . ' = :' . $column, [$column => $entityValue]);
            }
        } else {
            // Construct join clause
            $clause = new Query\Clause($this->query->connection);
            foreach ($relatedColumns as $idx => $column) {
                $clause->andX($relatedAlias . '.' . $column . ' = ' . $this->intermediateAlias . '.' . $this->intermediateColumns[$idx]);
            }

            $this->query
                ->columns($relatedAlias . '.*')
                ->from($relatedTable, $relatedAlias)
                ->innerJoin($this->intermediateTable, $clause, $this->intermediateAlias);
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
     * @param array ...$columns
     *
     * @return static
     */
    public function columns(... $columns)
    {
        $this->query->columns($columns);

        return $this;
    }

    /**
     * @param array ...$columns
     *
     * @return static
     */
    public function addColumns(... $columns)
    {
        $this->query->addColumns($columns);

        return $this;
    }

    /**
     * @param string      $table Database table to join
     * @param string      $on    Clause to join the table on
     * @param null|string $alias Alias for the table
     * @param string      $type  JOIN_DEFAULT|JOIN_LEFT|JOIN_RIGHT|JOIN_INNER|JOIN_FULL
     *
     * @return static
     */
    public function join($table, $on, $alias = null, $type = Query\Select::JOIN_DEFAULT)
    {
        $this->query->join($table, $on, $alias, $type);

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
        $this->query->leftJoin($table, $on, $alias);

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
        $this->query->rightJoin($table, $on, $alias);

        return $this;
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
        $this->query->fullJoin($table, $on, $alias);

        return $this;
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
        $this->query->innerJoin($table, $on, $alias);

        return $this;
    }

    /**
     * @see Select::where()
     *
     * @param string      $clause    A where clause
     * @param array|mixed $parameter A single, array of, or associated mapping of parameters
     *
     * @return static
     */
    public function where($clause, $parameter = null)
    {
        $this->query->where($clause, $parameter);

        return $this;
    }

    /**
     * @see Select::andWhere()
     *
     * @param string      $clause    A where clause
     * @param array|mixed $parameter A single, array of, or associated mapping of parameters
     *
     * @return static
     */
    public function andWhere($clause, $parameter = null)
    {
        $this->query->andWhere($clause, $parameter);

        return $this;
    }

    /**
     * @see Select::orWhere()
     *
     * @param string      $clause    A where clause
     * @param array|mixed $parameter A single, array of, or associated mapping of parameters
     *
     * @return static
     */
    public function orWhere($clause, $parameter = null)
    {
        $this->query->orWhere($clause, $parameter);

        return $this;
    }

    /**
     * @see Select::groupBy()
     * @param array ...$clauses
     *
     * @return static
     */
    public function groupBy(... $clauses)
    {
        $this->query->groupBy($clauses);

        return $this;
    }

    /**
     * @see Select::addGroupBy()
     * @param array ...$clauses
     *
     * @return static
     */
    public function addGroupBy(... $clauses)
    {
        $this->query->addGroupBy($clauses);

        return $this;
    }

    /**
     * @see Select::orderBy()
     * @param array ...$clauses
     *
     * @return static
     */
    public function orderBy(... $clauses)
    {
        $this->query->orderBy($clauses);

        return $this;
    }

    /**
     * @see Select::addOrderBy()
     * @param array ...$clauses
     *
     * @return static
     */
    public function addOrderBy(... $clauses)
    {
        $this->query->addOrderBy($clauses);

        return $this;
    }
}