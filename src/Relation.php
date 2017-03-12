<?php

namespace MadeSimple\Database;

use MadeSimple\Database\Statement\Query;
use MadeSimple\Database\Statement\Query\Select;

/**
 * Class Relation
 *
 * @package MadeSimple\Database
 * @author  Peter Scopes <peter.scopes@gmail.com>
 */
abstract class Relation
{
    /**
     * @var Entity
     */
    protected $entity;

    /**
     * @var string
     */
    protected $entityAlias;

    /**
     * @var Entity
     */
    protected $relative;

    /**
     * @var string
     */
    protected $relativeAlias;

    /**
     * @var string
     */
    protected $clause;

    /**
     * @var Select
     */
    protected $query;

    /**
     * Relation constructor.
     *
     * @param Entity      $entity
     * @param string      $relative
     * @param string      $clause
     * @param null|string $entityAlias
     * @param null|string $relativeAlias
     */
    public function __construct($entity, $relative, $clause, $entityAlias = null, $relativeAlias = null)
    {
        $this->entity        = $entity;
        $this->relative      = new $relative();
        $this->clause        = $clause;
        $this->entityAlias   = $entityAlias;
        $this->relativeAlias = $relativeAlias;
        $this->query         = $this->initialiseQuery($entity->connection);
    }

    /**
     * Initialise the relations query so that select columns and where clauses can be added to or replaced after the
     * construction. We delay the join onto the related table until query is called so that intermediate joins can be
     * added (allowing for relations through other related tables).
     *
     * @see Relation::query()
     * @param Connection $connection
     *
     * @return Query
     */
    protected function initialiseQuery(Connection $connection)
    {
        $select = $connection->select();

        $relativeTable = $this->relative->getMap()->tableName();
        $entityTable   = $this->entity->getMap()->tableName();
        $relativeAlias = null !== $this->relativeAlias ? $this->relativeAlias : $relativeTable;
        $entityAlias   = null !== $this->entityAlias ? $this->entityAlias : $entityTable;

        // Select from the relative table
        $select
            ->columns($relativeAlias . '.*')
            ->from($relativeTable, $relativeAlias);

        // Construct the where clause(s)
        foreach ($this->entity->getMap()->primaryKeys() as $dbKey => $entityKey) {
            $select->andWhere($entityAlias.'.'.$dbKey.' = :'.$dbKey, [$dbKey => $this->entity->{$entityKey}]);
        }

        return $select;
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
    public function join($table, $on, $alias = null, $type = Select::JOIN_DEFAULT)
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
        return $this->join($table, $on, $alias, Select::JOIN_LEFT);
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
        return $this->join($table, $on, $alias, Select::JOIN_RIGHT);
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
        return $this->join($table, $on, $alias, Select::JOIN_FULL);
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
        return $this->join($table, $on, $alias, Select::JOIN_INNER);
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

    /**
     * Construct the select query.
     *
     * @return Select
     */
    public function query()
    {
        $entityTable = $this->entity->getMap()->tableName();
        $entityAlias = null !== $this->entityAlias ? $this->entityAlias : $entityTable;
        $query = (clone $this->query);
        return $query
            // Join on the entity table
            ->join($entityTable, $this->clause, $entityAlias);;
    }

    /**
     * Fetch the relation(s).
     *
     * @return mixed
     */
    public abstract function fetch();
}