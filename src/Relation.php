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
     * @var array[]
     */
    protected $joins = [];

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

        $this->select = new Select(Connection::get());
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
     * @see Select::orWhere()
     *
     * @param string[]    $clauses   Array of where clauses
     * @param array|mixed $parameter A single, array of, or associated mapping of parameters
     *
     * @return Relation
     */
    public function orWhere(array $clauses, $parameter = null)
    {
        $this->select->orWhere($clauses, $parameter);

        return $this;
    }

    /**
     * @see Select::treeWhere()
     *
     * @param string $conjunction
     * @param array  $tree
     *
     * @return Relation
     */
    public function treeWhere($conjunction, array $tree)
    {
        $this->select->treeWhere($conjunction, $tree);

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
            $select->where(sprintf('%1$s.%2$s = :%2$s', $entityAlias, $dbKey), [$dbKey => $this->entity->{$entityKey}]);
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