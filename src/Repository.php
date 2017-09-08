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
     * @return Collection
     */
    public function findBy(array $columns = [], array $order = [])
    {
        $select = $this->connection->select()->columns('*')->from($this->entityMap->tableName(), 't');

        foreach ($columns as $column => $value) {
            $select->where('t.'.$column, '=', $value);
        }
        foreach ($order as $column => $direction) {
            $select->orderBy($column, $direction);
        }

        $select->query();
        $items = [];
        while (($row = $select->fetch(PDO::FETCH_ASSOC))) {
            $entity  = $this->reflection->newInstanceArgs([$this->pool]);
            $items[] = $entity->populate($row, $this->entityMap);
        }

        return new Collection($items);
    }

    /**
     * @param array $columns
     * @param array $order   Associative array column to direction
     *
     * @return null|object||Entity
     */
    public function findOneBy(array $columns = [], array $order = [])
    {
        $select = $this->connection->select()->columns('*')->from($this->entityMap->tableName(), 't')->limit(1);

        foreach ($columns as $column => $value) {
            $select->where('t.'.$column, '=', $value);
        }
        foreach ($order as $column => $direction) {
            $select->orderBy($column, $direction);
        }

        if (($row = $select->query()->fetch(PDO::FETCH_ASSOC))) {
            $entity  = $this->reflection->newInstanceArgs([$this->pool]);
            return $entity->populate($row, $this->entityMap);
        }

        return null;
    }
}