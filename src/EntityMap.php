<?php

namespace MadeSimple\Database;

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
     * @var string[]
     */
    protected $columnRemap = [];

    /**
     * DatabaseMap constructor.
     *
     * @param string   $tableName DB table name
     * @param string[] $keyMap    Map of DB keys to Entity properties
     * @param string[] $columnMap Map of DB columns to Entity properties (merged with $keyMap)
     */
    public function __construct($tableName, array $keyMap, array $columnMap)
    {
        $this->tableName   = $tableName;
        $this->keyMap      = $this->keyCheck($keyMap);
        $this->columnMap   = array_replace($this->keyMap, $this->keyCheck($columnMap), $this->keyMap);
        $this->columnRemap = $this->remapCheck($keyMap, $columnMap);
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
     * @param bool $withPrimaryKeys
     * @return string[]
     */
    public function columnMap($withPrimaryKeys = true)
    {
        return $withPrimaryKeys
            ? $this->columnMap
            : array_diff_key($this->columnMap, $this->keyMap);
    }

    /**
     * @return string[]
     */
    public function columnRemap()
    {
        return $this->columnRemap;
    }

    /**
     * @param bool $withPrimaryKeys
     * @return string[] DB columns
     */
    public function columns($withPrimaryKeys = true)
    {
        return $withPrimaryKeys
            ? array_keys($this->columnMap)
            : array_keys(array_diff_key($this->columnMap, $this->keyMap));
    }

    /**
     * @param bool $withPrimaryKeys
     * @return string[] Entity properties
     */
    public function properties($withPrimaryKeys = true)
    {
        return $withPrimaryKeys
            ? array_values($this->columnMap)
            : array_values(array_diff_key($this->columnMap, $this->keyMap));
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

    /**
     * Creates a new associative array of the given $columns where
     * is_int columns are replaced by the value, i.e.:
     * [
     *     'foo',
     *     'bar_bar' => 'barBar'
     * ]
     * becomes:
     * [
     *     'foo' => 'foo',
     *     'bar_bar' => 'barBar'
     * ]
     *
     * @param array $columns
     *
     * @return array
     */
    protected function keyCheck(array $columns)
    {
        $map = [];
        foreach ($columns as $k => $v) {
            $map[is_int($k) ? $v : $k] = $v;
        }
        return $map;
    }

    /**
     * @param array $keyMap
     * @param array $columnMap
     * @return array
     */
    protected function remapCheck(array $keyMap, array $columnMap)
    {
        $remap = [];
        foreach (array_merge($keyMap, $columnMap) as $k => $v) {
            if (!is_int($k)) {
                $remap[$k] = $v;
            }
        }
        return $remap;
    }
}