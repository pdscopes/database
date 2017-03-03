<?php

namespace Tests\Unit;

use MadeSimple\Database\EntityMap;
use Tests\TestCase;

class EntityMapTest extends TestCase
{
    /**
     * Test getting the table name.
     */
    public function testTableName()
    {
        $entityMap = new EntityMap('table_name', [], []);
        $this->assertEquals('table_name', $entityMap->tableName());
    }

    /**
     * Test getting the primary keys.
     *
     * @param array $keys
     * @dataProvider primaryKeysDataProvider
     */
    public function testPrimaryKeys($keys)
    {
        $entityMap = new EntityMap('', $keys, []);
        $this->assertEquals($keys, $entityMap->primaryKeys());
    }

    /**
     * Test getting a particular primary key.
     *
     * @param array   $keys
     * @param integer $index
     * @param string  $key
     * @dataProvider primaryKeysDataProvider
     */
    public function testPrimaryKey($keys, $index, $key)
    {
        $entityMap = new EntityMap('', $keys, []);
        $this->assertEquals($key, $entityMap->primaryKey($index));
    }

    /**
     * Test getting the column map.
     *
     * @param array $columnMap
     * @dataProvider columnMapDataProvider
     */
    public function testColumnMap($columnMap)
    {
        $entityMap = new EntityMap('', [], $columnMap);
        $this->assertEquals($columnMap, $entityMap->columnMap());
    }

    /**
     * Test getting the database columns.
     *
     * @param $columnMap
     * @dataProvider columnMapDataProvider
     */
    public function testColumns($columnMap)
    {
        $entityMap = new EntityMap('', [], $columnMap);
        $this->assertEquals(array_keys($columnMap), $entityMap->columns());
    }


    public function primaryKeysDataProvider()
    {
        return [
            [['id' => 'id'], 0, 'id'],

            [['user_id' => 'userId', 'company_id' => 'companyId'], 0, 'userId'],
            [['user_id' => 'userId', 'company_id' => 'companyId'], 1, 'companyId'],
        ];
    }
    public function columnMapDataProvider()
    {
        return [
            [['id' => 'id', 'user_id' => 'userId', 'foo' => 'bar']],
        ];
    }
}