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
     * @param Connection $connection
     * @param string     $className
     */
    public function __construct(Connection $connection, $className)
    {
        $this->connection = $connection;
        $this->className  = $className;
        $this->reflection = new ReflectionClass($className);

        /** @var Entity $prototype */
        $prototype       = $this->reflection->newInstance();
        $this->entityMap = $prototype->getEntityMap();
    }

    /**
     * @param array $columns
     * @param array $order
     *
     * @return array|Entity[]
     */
    public function findBy(array $columns = [], array $order = [])
    {
        $select = $this->connection->select()->columns()->from($this->entityMap->getTableName(), 't');

        foreach ($columns as $column => $value) {
            $select->where('t.'.$column.' = ?', $value);
        }
        $select->orderBy($order);

        $stmt  = $select->execute();
        $items = [];
        while (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
            $items[] = $this->reflection->newInstanceArgs([$row]);
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
        $select = $this->connection->select()->columns()->from($this->entityMap->getTableName(), 't')->limit(1);

        foreach ($columns as $column => $value) {
            $select->where('t.'.$column.' = :'.$column, [$column => $value]);
        }
        $select->orderBy($order);

        $stmt = $select->execute();
        if (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
            return $this->reflection->newInstanceArgs([$row]);
        }

        return null;
    }
}