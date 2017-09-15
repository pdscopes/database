<?php

namespace MadeSimple\Database;

use JsonSerializable;
use MadeSimple\Arrays\Arrayable;
use MadeSimple\Database\Entity\PropertiesToArrayTrait;
use MadeSimple\Database\Entity\CastPropertyTrait;
use PDO;

abstract class Entity implements JsonSerializable, Arrayable, Jsonable
{
    use CastPropertyTrait, PropertiesToArrayTrait;

    /**
     * @var string
     */
    public static $connection = null;

    /**
     * @var Pool
     */
    public $pool;

    /**
     * @var bool True if create has been called
     * @see Entity::create
     */
    public $createdRecently = false;

    /**
     * Entity constructor.
     *
     * @param Pool|null  $pool
     * @param array|null $row
     */
    public function __construct(Pool $pool = null, array $row = null)
    {
        $this->pool = $pool;

        if (null !== $row) {
            $this->populate($row);
        }
    }

    /**
     * @param Pool $pool
     *
     * @return Entity
     */
    public function setPool(Pool $pool)
    {
        $this->pool = $pool;

        return $this;
    }

    /**
     * @param array     $row
     * @param EntityMap $map
     *
     * @return Entity
     */
    public function populate(array &$row, EntityMap $map = null)
    {
        $map = $map ?? $this->getMap();
        foreach ($map->columnMap() as $dbName => $propName) {
            if (!isset($row[$dbName])) {
                continue;
            }
            $this->{$propName} = $row[$dbName];
        }

        return $this;
    }

    /**
     * @return EntityMap
     */
    public abstract function getMap();

    /**
     * Creates the entity if the primary key(s) are null, otherwise updates the entity.
     *
     * @return bool True if the entity was successfully persisted
     */
    public function persist()
    {
        $map = $this->getMap();
        if (count($map->primaryKeys()) != 1) {
            throw new \InvalidArgumentException('Cannot persist');
        }

        // Check if primary keys are set
        $notExists = array_reduce(
            $map->primaryKeys(), function ($carry, $item) {
                return $carry && null === $this->{$item};
            }, true
        );

        return $notExists ? $this->create() : $this->update();
    }

    /**
     * @return bool True if the entity was successfully created
     */
    public function create()
    {
        $connection = $this->pool->get(static::$connection);

        $map    = $this->getMap();
        $values = [];
        foreach ($map->columnMap() as $property) {
            $values[] = $this->{$property};
        }
        $insert = $connection->insert()
            ->into($map->tableName())
            ->columns($map->columns())
            ->values($values)
            ->query();

        if (false === $insert) {
            return false;
        }

        if (count($map->primaryKeys()) === 1 && null === $this->{$map->primaryKey(0)}) {
            $this->{$map->primaryKey(0)} = $insert->lastInsertId();
        }

        return $this->createdRecently = true;
    }

    /**
     * @return bool True if the entity was successfully updated
     */
    public function update()
    {
        $connection = $this->pool->get(static::$connection);

        $map    = $this->getMap();
        $values = [];
        foreach ($map->columnMap() as $dbField => $property) {
            $values[$dbField] = $this->{$property};
        }
        $update = $connection->update()
            ->table($map->tableName())
            ->columns($values);

        foreach ($map->primaryKeys() as $idx => $key) {
            $update->where($idx, '=', $this->{$key});
        }


        return 1 === $update->query()->affectedRows();
    }

    /**
     * @param null|mixed $primaryKey Null to use the set primary key, other the given primary key
     *
     * @return bool True if the entity was successfully read (populated)
     */
    public function read($primaryKey = null)
    {
        $connection = $this->pool->get(static::$connection);

        $map    = $this->getMap();
        $select = $connection->select()->columns('*')->from($map->tableName(), 't')->limit(1);

        if (null !== $primaryKey && !is_array($primaryKey)) {
            $primaryKey = array_combine(array_keys($map->primaryKeys()), [$primaryKey]);
        }
        foreach ($map->primaryKeys() as $idx => $key) {
            $select->where('t.' . $idx, '=', null !== $primaryKey ? $primaryKey[$idx] : $this->{$key});
        }

        $row = $select->query()->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_FIRST);
        if (false === $row) {
            throw new \InvalidArgumentException('Invalid table name or primary key name/value');
        }

        $this->populate($row);

        return true;
    }

    /**
     * @param null|mixed $primaryKey Null to use the set primary key, other the given primary key
     *
     * @return bool True if entity was successfully deleted
     */
    public function delete($primaryKey = null)
    {
        $connection = $this->pool->get(static::$connection);

        $map    = $this->getMap();
        $delete = $connection->delete()->from($map->tableName());

        if (null !== $primaryKey && !is_array($primaryKey)) {
            $primaryKey = array_combine(array_keys($map->primaryKeys()), [$primaryKey]);
        }
        foreach ($map->primaryKeys() as $idx => $key) {
            $delete->where($idx, '=', null !== $primaryKey ? $primaryKey[$idx] : $this->{$key});
        }

        return 1 === $delete->query()->affectedRows();
    }

    /**
     * @param array $columns Mapping from column name to value
     *
     * @return bool True if the entity was successfully found (populated)
     */
    public function find(array $columns)
    {
        $connection = $this->pool->get(static::$connection);

        $map    = $this->getMap();
        $select = $connection->select()->columns('*')->from($map->tableName(), 't')->limit(1);

        foreach ($columns as $column => $value) {
            $select->where('t.' . $column, '=', $value);
        }

        $row = $select->query()->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_FIRST);

        return $row && $this->populate($row);
    }

    /**
     * @InheritDoc
     */
    public function toJson($options = 0, $depth = 512)
    {
        return json_encode($this->jsonSerialize(), $options, $depth);
    }

    /**
     * @InheritDoc
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->propertiesToArray($this->getMap()->columnMap());
    }
}