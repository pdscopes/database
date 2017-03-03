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
    /**
     * @var Connection
     */
    public $connection;

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
     * @param Connection|null $connection
     * @param array|null      $row
     */
    public function __construct(Connection $connection = null, array $row = null)
    {
        $this->connection = $connection;
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
     * @param Connection|null $connection
     *
     * @return bool True if the entity was successfully persisted
     */
    public function persist(Connection $connection = null)
    {
        $entityMap = $this->getEntityMap();
        if (count($entityMap->getPrimaryKeys()) != 1) {
            throw new \InvalidArgumentException('Cannot persist');
        }

        if (array_reduce($entityMap->getPrimaryKeys(), function ($carry, $item) { return $carry && null === $this->{$item}; }, true)) {
            return $this->create($connection);
        }

        return $this->update($connection);
    }

    /**
     * @param Connection|null $connection
     *
     * @return bool True if the entity was successfully created
     */
    public function create(Connection $connection = null)
    {
        $connection = $connection ?: $this->connection;

        $entityMap = $this->getEntityMap();
        $values    = [];
        foreach ($entityMap->getColumnMap() as $property) {
            $values[$property] = $this->{$property};
        }
        $stmt = $connection->insert()
            ->into($entityMap->getTableName())
            ->columns($entityMap->getColumns())
            ->values($values)
            ->execute();

        if (false === $stmt) {
            return false;
        }

        if (count($entityMap->getPrimaryKeys()) == 1) {
            $this->{$entityMap->getPrimaryKey(0)} = $connection->lastInsertId();
        }

        return $this->createdRecently = true;
    }

    /**
     * @param Connection|null $connection
     *
     * @return bool True if the entity was successfully updated
     */
    public function update(Connection $connection = null)
    {
        $connection = $connection ?: $this->connection;

        $entityMap = $this->getEntityMap();
        $values    = [];
        foreach ($entityMap->getColumnMap() as $property) {
            $values[] = $this->{$property};
        }
        $update = $connection->update()
            ->table($entityMap->getTableName())
            ->columns($entityMap->getColumns())
            ->setParameters($values);

        foreach ($entityMap->getPrimaryKeys() as $idx => $key) {
            $update->andWhere($key . ' = ?', $this->{$key});
        }


        return false !== $update->execute();
    }

    /**
     * @param Connection|null $connection
     * @param null|mixed $primaryKey Null to use the set primary key, other the given primary key
     *
     * @return bool True if the entity was successfully read (populated)
     */
    public function read(Connection $connection = null, $primaryKey = null)
    {
        $connection = $connection ?: $this->connection;

        $entityMap = $this->getEntityMap();
        $select    = $connection->select()->columns('*')->from($entityMap->getTableName(), 't')->limit(1);

        if (null !== $primaryKey && !is_array($primaryKey)) {
            $primaryKey = array_combine(array_keys($entityMap->getPrimaryKeys()), [$primaryKey]);
        }
        foreach ($entityMap->getPrimaryKeys() as $idx => $key) {
            $select->andWhere('t.' . $key . ' = ?', null !== $primaryKey ? $primaryKey[$idx] : $this->{$key});
        }

        $row = $select->execute()->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_FIRST);
        if (false === $row) {
            throw new \InvalidArgumentException('Invalid table name or primary key name/value');
        }

        $this->populate($row);

        return true;
    }

    /**
     * @param Connection|null $connection
     *
     * @return bool True if entity was successfully deleted
     */
    public function delete(Connection $connection = null)
    {
        $connection = $connection ?: $this->connection;

        $entityMap = $this->getEntityMap();
        $delete    = $connection->delete()->from($entityMap->getTableName());

        foreach ($entityMap->getPrimaryKeys() as $key) {
            $delete->andWhere($key . '= ?', $this->{$key});
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