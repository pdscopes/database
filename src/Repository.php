<?php

namespace MadeSimple\Database;

use PDO;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

class Repository
{
    use LoggerAwareTrait;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var EntityMap
     */
    protected $entityMap;

    /**
     * Repository constructor.
     *
     * @param Pool   $pool
     * @param string $className
     */
    public function __construct(Pool $pool, $className)
    {
        $this->pool       = $pool;
        $this->className  = $className;
        $this->connection = $pool->get($className::$connection);
        $this->setLogger(new NullLogger);

        $this->entityMap = $className::map();
    }

    /**
     * @param array $columns
     * @param array $order   Associative array column to direction
     *
     * @return EntityCollection
     */
    public function findBy(array $columns = [], array $order = [])
    {
        $select = $this->buildFindByQuery($columns, $order);

        return new EntityCollection($select->fetchAll(PDO::FETCH_CLASS, $this->className, [$this->pool, true]));
    }

    /**
     * @param int   $limit    > 1
     * @param int   $page     > 1
     * @param array $columns
     * @param array $order    Associative array column to direction
     *
     * @return PaginatedCollection
     */
    public function paginatedFindBy($limit, $page, array $columns = [], array $order = [])
    {
        $select = $this->buildFindByQuery($columns, $order)
            ->limit(max(1, $limit))
            ->offset((max(1, $page)-1) * $limit);

        $items = $select->fetchAll(PDO::FETCH_CLASS, $this->className, [$this->pool, true]);

        return new PaginatedCollection($items, $page, $select->count());
    }

    /**
     * @param array $columns
     * @param array $order   Associative array column to direction
     *
     * @return null|object||Entity
     */
    public function findOneBy(array $columns = [], array $order = [])
    {
        $select = $this->buildFindByQuery($columns, $order)->limit(1);

        $entity = $select->setFetchMode(PDO::FETCH_CLASS, $this->className, [$this->pool, true])->fetch();

        return $entity ? $entity : null;
    }

    /**
     * @param array  $columns
     * @param array  $order
     * @param string $alias
     *
     * @return Query\Select
     */
    protected function buildFindByQuery(array $columns = [], array $order = [], $alias = 't')
    {
        $select = $this->connection->select()->columns($alias . '.*')->from($this->entityMap->tableName(), $alias);

        foreach ($columns as $column => $value) {
            $select->where($alias.'.'.$column, '=', $value);
        }
        foreach ($order as $column => $direction) {
            if (is_int($column)) {
                $column    = $direction;
                $direction = 'asc';
            }
            $select->orderBy($column, $direction);
        }

        return $select;
    }
}