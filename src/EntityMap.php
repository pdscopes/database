<?php

namespace MadeSimple\Database;

/**
 * Class DatabaseMap
 *
 * @package MadeSimple\Database
 * @author  Peter Scopes
 */
class EntityMap
{
    /**
     * @var string
     */
    protected $tableName;

    /**
     * @var string[]
     */
    protected $keyMap;

    /**
     * @var string[]
     */
    protected $columnMap;

    /**
     * DatabaseMap constructor.
     *
     * @param string   $tableName DB table name
     * @param string[] $keyMap    Map of DB keys to Entity properties
     * @param string[] $columnMap Map of DB columns to Entity properties (merged with $keyMap)
     */
    public function __construct($tableName, array $keyMap, array $columnMap)
    {
        $this->tableName = $tableName;
        $this->keyMap    = $keyMap;
        $this->columnMap = array_replace($keyMap, $columnMap, $keyMap);
    }

    /**
     * @return string
     */
    public function tableName()
    {
        return $this->tableName;
    }

    /**
     * @return string[]
     */
    public function primaryKeys()
    {
        return $this->keyMap;
    }

    /**
     * @param int $index
     *
     * @return string
     */
    public function primaryKey($index)
    {
        return array_values(array_slice($this->keyMap, $index, 1))[0];
    }

    /**
     * @return string[]
     */
    public function columnMap()
    {
        return $this->columnMap;
    }

    /**
     * @return string[] DB columns
     */
    public function columns()
    {
        return array_keys($this->columnMap);
    }

    /**
     * @return string[] Entity properties
     */
    public function properties()
    {
        return array_values($this->columnMap);
    }

    /**
     * @param string $column
     *
     * @return string
     */
    public function property($column)
    {
        return $this->columnMap[$column];
    }
}