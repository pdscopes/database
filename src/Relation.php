<?php

namespace MadeSimple\Database;

use MadeSimple\Database\QueryBuilder\Select;

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
    protected $select;

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

        $this->select = new Select($entity->connection);
    }

    /**
     * @param string      $table Database table to join
     * @param string      $on    Clause to join the table on
     * @param null|string $alias Alias for the table
     * @param string      $type  JOIN_DEFAULT|JOIN_LEFT|JOIN_RIGHT|JOIN_INNER|JOIN_FULL
     *
     * @return Relation
     */
    public function join($table, $on, $alias = null, $type = Select::JOIN_DEFAULT)
    {
        $this->select->join($table, $on, $alias, $type);

        return $this;
    }

    /**
     * @param string      $table Database table to join
     * @param string      $on    Clause to join the table on
     * @param null|string $alias Alias for the table
     *
     * @return Relation
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
     * @return Relation
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
     * @return Relation
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
     * @return Relation
     */
    public function where($clause, $parameter = null)
    {
        $this->select->where($clause, $parameter);

        return $this;
    }

    /**
     * @see Select::andWhere()
     *
     * @param string      $clause    A where clause
     * @param array|mixed $parameter A single, array of, or associated mapping of parameters
     *
     * @return Relation
     */
    public function andWhere($clause, $parameter = null)
    {
        $this->select->andWhere($clause, $parameter);

        return $this;
    }

    /**
     * @see Select::orWhere()
     *
     * @param string      $clause    A where clause
     * @param array|mixed $parameter A single, array of, or associated mapping of parameters
     *
     * @return Relation
     */
    public function orWhere($clause, $parameter = null)
    {
        $this->select->orWhere($clause, $parameter);

        return $this;
    }

    /**
     * Construct the select query.
     *
     * @return Select
     */
    public function query()
    {
        $relativeTable = $this->relative->getEntityMap()->getTableName();
        $entityTable   = $this->entity->getEntityMap()->getTableName();
        $relativeAlias = null !== $this->relativeAlias ? $this->relativeAlias : $relativeTable;
        $entityAlias   = null !== $this->entityAlias ? $this->entityAlias : $entityTable;

        // Select from the relative table
        $select = (clone $this->select)
            ->columns($relativeAlias . '.*')
            ->from($relativeTable, $relativeAlias);

        // Join on the entity table
        $select->join($entityTable, $this->clause, $entityAlias);

        // Construct the where clause(s)
        foreach ($this->entity->getEntityMap()->getPrimaryKeys() as $dbKey => $entityKey) {
            $select->andWhere($entityAlias.'.'.$dbKey.' = :'.$dbKey, [$dbKey => $this->entity->{$entityKey}]);
        }

        return $select;
    }

    /**
     * Fetch the relation(s).
     *
     * @return mixed
     */
    public abstract function fetch();
}