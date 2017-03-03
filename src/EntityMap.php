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
    protected $primaryKeys;

    /**
     * @var string[]
     */
    protected $columnMap;

    /**
     * DatabaseMap constructor.
     *
     * @param string   $tableName
     * @param string[] $primaryKey
     * @param string[] $columnMap
     */
    public function __construct($tableName, array $primaryKey, array $columnMap)
    {
        $this->tableName   = $tableName;
        $this->primaryKeys = $primaryKey;
        $this->columnMap   = $columnMap;
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
        return $this->primaryKeys;
    }

    /**
     * @param int $index
     *
     * @return string
     */
    public function primaryKey($index)
    {
        return array_values(array_slice($this->primaryKeys, $index, 1))[0];
    }

    /**
     * @return string[]
     */
    public function columnMap()
    {
        return $this->columnMap;
    }

    /**
     * @return string[]
     */
    public function columns()
    {
        return array_keys($this->columnMap);
    }
}