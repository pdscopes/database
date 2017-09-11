<?php

namespace MadeSimple\Database\Tests\Unit;

use MadeSimple\Database\EntityMap;
use MadeSimple\Database\Tests\TestCase;

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
     * @param array $keyMap
     *
     * @dataProvider keyMapDataProvider
     */
    public function testPrimaryKeys($keyMap)
    {
        $entityMap = new EntityMap('', $keyMap, []);
        $this->assertEquals($keyMap, $entityMap->primaryKeys());
    }

    /**
     * Test getting a particular primary key.
     *
     * @param array   $keyMap
     * @param integer $index
     * @param string  $key
     *
     * @dataProvider keyMapDataProvider
     */
    public function testPrimaryKey($keyMap, $index, $key)
    {
        $entityMap = new EntityMap('', $keyMap, []);
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
     * @param array $columnMap
     *
     * @dataProvider columnMapDataProvider
     */
    public function testColumns($columnMap)
    {
        $entityMap = new EntityMap('', [], $columnMap);
        $this->assertEquals(array_keys($columnMap), $entityMap->columns());
    }

    /**
     * Test getting the entity properties.
     *
     * @param array $columnMap
     *
     * @dataProvider columnMapDataProvider
     */
    public function testProperties($columnMap)
    {
        $entityMap = new EntityMap('', [], $columnMap);
        $this->assertEquals(array_values($columnMap), $entityMap->properties());
    }


    public function keyMapDataProvider()
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