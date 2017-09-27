<?php

namespace MadeSimple\Database;

use JsonSerializable;
use MadeSimple\Arrays\Arr;
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
     * List of properties to be visible by default.
     *
     * @var array
     */
    protected $visible = [];

    /**
     * List of properties to be hidden by default.
     *
     * @var array
     */
    protected $hidden = [];

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
        foreach ($map->columnMap() as $column => $property) {
            $values[$column] = $this->{$property};
        }
        // Filter null values - being created so pointless
        $values = array_filter($values, function ($item) { return $item !== null; });
        $insert = $connection->insert()
            ->into($map->tableName())
            ->columns(array_keys($values))
            ->values(array_values($values))
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
     * @param string|array $properties
     *
     * @return bool True if the entity was successfully updated
     */
    public function update($properties = null)
    {
        // Calculate the columns to be updated
        $map     = $this->getMap();
        $columns = $map->columnMap(false);
        $columns = array_intersect($columns, (array) ($properties ?? $columns));
        if (empty($columns)) {
            return true;
        }

        // Retrieve the property values
        foreach ($columns as $dbField => $property) {
            $columns[$dbField] = $this->{$property};
        }
        $update = $this->pool->get(static::$connection)
            ->update()
            ->table($map->tableName())
            ->columns($columns);

        foreach ($map->primaryKeys() as $idx => $key) {
            $update->where($idx, '=', $this->{$key});
        }

        return 1 === $update->query()->affectedRows();
    }

    /**
     * @param null|mixed $keys Null to use the set primary key, an associative array to read other unique columns, or
     *                         a normal array to pass in primary key value(s)
     *
     * @return static
     */
    public function read($keys = null)
    {
        $map    = $this->getMap();
        $select = $this->pool->get(static::$connection)->select()->from($map->tableName())->limit(1);


        $keys = (array) $keys;
        // Entity::read()
        if (empty($keys)) {
            $keys = array_combine(array_keys($map->primaryKeys()), $this->propertiesToArray($map->primaryKeys()));
        }
        // Entity::read(15) or Entity::read([15,7])
        elseif (!Arr::isAssoc($keys)) {
            $keys = array_combine(array_keys($map->primaryKeys()), $keys);
        }
        // Entity::read(['uuid' => '123'])

        foreach ($keys as $column => $value) {
            $select->where($column, '=', $value);
        }

        $row = $select->query()->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_FIRST);
        if (false === $row) {
            throw new \InvalidArgumentException('Invalid table name or primary key name/value');
        }

        return $this->populate($row);;
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