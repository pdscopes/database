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
     * @var EntityMap[]
     */
    protected static $maps = [];

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
     * @param bool       $remap
     */
    public function __construct(Pool $pool = null, $remap = false)
    {
        $this->pool = $pool;

        if ($remap === true) {
            $this->remap();
        }
    }

    /**
     * When an entity sleeps we only needs it's primary keys.
     *
     * @return string[]
     */
    public function __sleep()
    {
        return static::map()->primaryKeys();
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
     * Remap the entity from a PDO::FETCH_CLASS object to the one defined by
     * the EntityMap.
     */
    protected function remap()
    {
        foreach (static::map()->columnRemap() as $column => $property) {
            if (!property_exists($this, $column)) {
                continue;
            }
            $this->{$property} = $this->{$column};
            unset($this->{$column});
        }
    }

    /**
     * @param array $row
     *
     * @return Entity
     */
    public function populate(array $row)
    {
        foreach (static::map()->columnMap() as $dbName => $propName) {
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
    public static function map()
    {
        if (!isset(static::$maps[static::class])) {
            static::$maps[static::class] = static::getMap();
        }
        return static::$maps[static::class];
    }

    /**
     * @return EntityMap
     */
    protected static abstract function getMap();

    /**
     * Creates the entity if the primary key(s) are null, otherwise updates the entity.
     *
     * @return bool True if the entity was successfully persisted
     */
    public function persist()
    {
        $map = static::map();
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

        $map    = static::map();
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
        $map     = static::map();
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
     * @see Entity::find
     * @return static
     */
    public function read($keys = null)
    {
        $map    = static::map();
        $select = $this->pool->get(static::$connection)->select()->from($map->tableName())->limit(1);


        $keys = (array) $keys;
        // Entity::read()
        if (empty($keys)) {
            $keys = $map->primaryKeys($this);
        }
        // Entity::read(15) or Entity::read([15,7])
        elseif (!Arr::isAssoc($keys)) {
            $keys = array_combine(array_keys($map->primaryKeys()), $keys);
        }
        // Entity::read(['uuid' => '123'])

        foreach ($keys as $column => $value) {
            $select->where($column, '=', $value);
        }

        $row = $select->query()->fetch(PDO::FETCH_ASSOC);
        if (false === $row) {
            throw new \InvalidArgumentException('Invalid table name or primary key name/value');
        }

        return $this->populate($row);
    }

    /**
     * @param null|mixed $primaryKey Null to use the set primary key, other the given primary key
     *
     * @return bool True if entity was successfully deleted
     */
    public function delete($primaryKey = null)
    {
        $connection = $this->pool->get(static::$connection);

        $map    = static::map();
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
     * @see Entity::read
     * @return static True if the entity was successfully found (populated)
     */
    public function find(array $columns)
    {
        $connection = $this->pool->get(static::$connection);

        $map    = static::map();
        $select = $connection->select()->columns('*')->from($map->tableName(), 't')->limit(1);

        foreach ($columns as $column => $value) {
            $select->where('t.' . $column, '=', $value);
        }

        $row = $select->query()->fetch(PDO::FETCH_ASSOC);
        if (false === $row) {
            throw new \InvalidArgumentException('Invalid table name or primary key name/value');
        }

        return $this->populate($row);
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
        return $this->propertiesToArray(static::map()->columnMap());
    }
}