<?php

namespace MadeSimple\Database;

use JsonSerializable;
use PDO;

/**
 * Class Entity
 *
 * @package MadeSimple\Database
 * @author  Peter Scopes
 */
abstract class Entity implements JsonSerializable
{
    const db = null;

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
     * List of properties to be cast.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * Entity constructor.
     *
     * @param array|null $row
     */
    public function __construct(array $row = null)
    {
        if (null !== $row) {
            $this->populate($row);
        }
    }

    /**
     * @param array $row
     *
     * @return Entity
     */
    public function populate(array &$row)
    {
        $entityMap = $this->getEntityMap();
        foreach ($entityMap->getColumnMap() as $dbName => $propName) {
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
    public abstract function getEntityMap();

    /**
     * Creates the entity if the primary key(s) are null, otherwise updates the entity.
     *
     * @return bool True if the entity was successfully persisted
     */
    public function persist()
    {
        $entityMap = $this->getEntityMap();
        if (count($entityMap->getPrimaryKeys()) != 1) {
            throw new \InvalidArgumentException('Cannot persist');
        }

        if (is_null($this->{$entityMap->getPrimaryKey(0)})) {
            return $this->create();
        }

        return $this->update();
    }

    /**
     * @return bool True if the entity was successfully created
     */
    public function create()
    {
        $entityMap = $this->getEntityMap();
        $values    = [];
        foreach ($entityMap->getColumnMap() as $property) {
            $values[$property] = $this->{$property};
        }
        $stmt = Connection::insert(self::db)
            ->into($entityMap->getTableName())
            ->columns($entityMap->getColumns())
            ->values($values)
            ->execute();

        if (false === $stmt) {
            return false;
        }

        if (count($entityMap->getPrimaryKeys()) == 1) {
            $this->{$entityMap->getPrimaryKey(0)} = Connection::lastInsertId(self::db);
        }

        return $this->createdRecently = true;
    }

    /**
     * @return bool True if the entity was successfully updated
     */
    public function update()
    {
        $entityMap = $this->getEntityMap();
        $values    = [];
        foreach ($entityMap->getColumnMap() as $property) {
            $values[] = $this->{$property};
        }
        $update = Connection::update(self::db)
            ->table($entityMap->getTableName())
            ->set($entityMap->getColumns())
            ->setParameters($values);

        foreach ($entityMap->getPrimaryKeys() as $idx => $key) {
            $update->where(sprintf('`%s` = ?', $key), $this->{$key});
        }


        return false !== $update->execute();
    }

    /**
     * @param null|mixed $primaryKey Null to use the set primary key, other the given primary key
     *
     * @return bool True if the entity was successfully read (populated)
     */
    public function read($primaryKey = null)
    {
        $entityMap = $this->getEntityMap();
        $select    = Connection::select(self::db)
            ->columns()
            ->from($entityMap->getTableName(), 't')
            ->limit(1);

        if (!is_null($primaryKey) && !is_array($primaryKey)) {
            $primaryKey = array_combine(array_keys($entityMap->getPrimaryKeys()), [$primaryKey]);
        }
        foreach ($entityMap->getPrimaryKeys() as $idx => $key) {
            $select->where(sprintf('`t`.`%s` = ?', $key), !is_null($primaryKey) ? $primaryKey[$idx] : $this->{$key});
        }

        $row = $select->execute()->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_FIRST);
        if (false === $row) {
            throw new \InvalidArgumentException('Invalid table name or primary key name/value');
        }

        $this->populate($row);

        return true;
    }

    /**
     * @return bool True if entity was successfully deleted
     */
    public function delete()
    {
        $entityMap = $this->getEntityMap();
        $delete    = Connection::delete(self::db)
            ->from($entityMap->getTableName());

        foreach ($entityMap->getPrimaryKeys() as $key) {
            $delete->column($key, $this->{$key});
        }

        return false !== $delete->execute();
    }

    /**
     * {@InheritDoc}
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
        $properties = [];
        foreach ($this->getEntityMap()->getColumnMap() as $property) {
            if (in_array($property, $this->hidden)) {
                continue;
            }
            $properties[$property] = $this->castProperty($property);
        }
        foreach ($this->visible as $property) {
            $properties[$property] = $this->castProperty($property);

        }

        return $properties;
    }

    /**
     * @param      $property
     * @param null $default
     *
     * @return array|float|null|string
     */
    protected function castProperty($property, $default = null)
    {
        if (!isset($this->{$property})) {
            return $default;
        }

        if (!isset($this->casts[$property])) {
            return $this->{$property};
        }

        switch ($this->casts[$property]) {
            case 'int':
            case 'integer':
                return (int) $this->{$property};

            case 'bool':
            case 'boolean':
                return (bool) $this->{$property};

            case 'double':
            case 'float':
            case 'real':
                return (float) $this->{$property};

            case 'string':
                return (string) $this->{$property};

            case 'array':
                return (array) $this->{$property};

            case 'json':
                return json_decode($this->{$property}, true);

            default:
                return $default;
        }
    }
}