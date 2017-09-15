<?php

namespace MadeSimple\Database;

use PDO;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ReflectionClass;

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
     * @var ReflectionClass
     */
    protected $reflection;

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
        $this->reflection = new ReflectionClass($className);
        $this->connection = $pool->get($this->reflection->getStaticPropertyValue('connection'));
        $this->setLogger(new NullLogger);

        /** @var Entity $prototype */
        $prototype       = $this->reflection->newInstance();
        $this->entityMap = $prototype->getMap();
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

        $select->query();
        $items = [];
        while (($row = $select->fetch(PDO::FETCH_ASSOC))) {
            $entity  = $this->reflection->newInstanceArgs([$this->pool]);
            $items[] = $entity->populate($row, $this->entityMap);
        }

        return new EntityCollection($items);
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

        $select->query();
        $items = [];
        while (($row = $select->fetch(PDO::FETCH_ASSOC))) {
            $entity  = $this->reflection->newInstanceArgs([$this->pool]);
            $items[] = $entity->populate($row, $this->entityMap);
        }

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

        if (($row = $select->query()->fetch(PDO::FETCH_ASSOC))) {
            $entity  = $this->reflection->newInstanceArgs([$this->pool]);
            return $entity->populate($row, $this->entityMap);
        }

        return null;
    }

    /**
     * @param array $columns
     * @param array $order
     *
     * @return Query\Select
     */
    protected function buildFindByQuery(array $columns = [], array $order = [])
    {
        $select = $this->connection->select()->columns('*')->from($this->entityMap->tableName(), 't');

        foreach ($columns as $column => $value) {
            $select->where('t.'.$column, '=', $value);
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