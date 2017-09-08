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
     * DatabaseMap constructor.
     *
     * @param string   $tableName DB table name
     * @param string[] $keyMap    Map of DB keys to Entity properties
     * @param string[] $columnMap Map of DB columns to Entity properties (merged with $keyMap)
     */
    public function __construct($tableName, array $keyMap, array $columnMap)
    {
        $this->tableName = $tableName;
        $this->keyMap    = $this->keyCheck($keyMap);
        $this->columnMap = array_replace($this->keyMap, $this->keyCheck($columnMap), $this->keyMap);
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
        $array = [];
        foreach ($columns as $k => $v) {
            $array[is_int($k) ? $v : $k] = $v;
        }
        return $array;
    }
}