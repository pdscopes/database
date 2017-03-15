<?php

namespace MadeSimple\Database;

use PDO;
use ReflectionClass;

/**
 * Class Repository
 *
 * @package MadeSimple\Database
 * @author  Peter Scopes
 */
class Repository
{
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

        /** @var Entity $prototype */
        $prototype       = $this->reflection->newInstance();
        $this->entityMap = $prototype->getMap();
    }

    /**
     * @param array $columns
     * @param array $order
     *
     * @return array|Entity[]
     */
    public function findBy(array $columns = [], array $order = [])
    {
        $select = $this->connection->select()->columns('*')->from($this->entityMap->tableName(), 't');

        foreach ($columns as $column => $value) {
            $select->andWhere('t.'.$column.' = ?', $value);
        }
        $select->orderBy($order);

        $stmt  = $select->execute();
        $items = [];
        while (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
            $items[] = $this->reflection->newInstanceArgs([$this->pool, $row]);
        }

        return $items;
    }

    /**
     * @param array $columns
     * @param array $order
     *
     * @return null|object||Entity
     */
    public function findOneBy(array $columns = [], array $order = [])
    {
        $select = $this->connection->select()->columns('*')->from($this->entityMap->tableName(), 't')->limit(1);

        foreach ($columns as $column => $value) {
            $select->andWhere('t.'.$column.' = ?', $value);
        }
        $select->orderBy($order);

        $stmt = $select->execute();
        if (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
            return $this->reflection->newInstanceArgs([$this->pool, $row]);
        }

        return null;
    }
}