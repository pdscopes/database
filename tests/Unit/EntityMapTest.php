<?php

namespace MadeSimple\Database\Tests\Unit;

use MadeSimple\Database\Entity;
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
     * Test primaryKeys with an Entity returns a populated array of
     * that entity's primary keys.
     */
    public function testPrimaryKeysWithEntity()
    {
        $entityMap = EntityMapEntity::map();
        $entity    = new EntityMapEntity;
        $entity->id = 15;

        $this->assertEquals(['id' => 15], $entityMap->primaryKeys($entity));
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
     * Test getting the populate map.
     *
     * @param array $keyMap
     * @param array $columnMap
     * @param array $linkedMap
     * @param array $populateMap
     * @dataProvider populateMapDataProvider
     */
    public function testPopulateMap($keyMap, $columnMap, $linkedMap, $populateMap)
    {
        $entityMap = new EntityMap('', $keyMap, $columnMap, $linkedMap);
        $this->assertEquals($populateMap, $entityMap->populateMap());
    }

    /**
     * Test getting the populate map.
     *
     * @param array $keyMap
     * @param array $columnMap
     * @param array $linkedMap
     * @param array $columnRemap
     * @dataProvider columnRemapDataProvider
     */
    public function testColumnRemap($keyMap, $columnMap, $linkedMap, $columnRemap)
    {
        $entityMap = new EntityMap('', $keyMap, $columnMap, $linkedMap);
        $this->assertEquals($columnRemap, $entityMap->columnRemap());
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
    public function populateMapDataProvider()
    {
        return [
            [[], [], [], []],

            [['key'], [], [], ['key' => 'key']],
            [['diffKey' => 'key'], [], [], ['diffKey' => 'key']],

            [[], ['column'], [], ['column' => 'column']],
            [[], ['diffColumn' => 'column'], [], ['diffColumn' => 'column']],

            [[], [], ['linked'], ['linked' => 'linked']],
            [[], [], ['diffLinked' => 'linked'], ['diffLinked' => 'linked']],

            [['key'], ['diffColumn' => 'column'], [], ['key' => 'key', 'diffColumn' => 'column']],
            [['key'], ['column'], ['linked'], ['key' => 'key', 'column' => 'column', 'linked' => 'linked']],
        ];
    }
    public function columnRemapDataProvider()
    {
        return [
            [[], [], [], []],

            [['key'], [], [], []],
            [['diffKey' => 'key'], [], [], ['diffKey' => 'key']],

            [[], ['column'], [], []],
            [[], ['diffColumn' => 'column'], [], ['diffColumn' => 'column']],

            [[], [], ['linked'], []],
            [[], [], ['diffLinked' => 'linked'], ['diffLinked' => 'linked']],

            [['key'], ['diffColumn' => 'column'], [], ['diffColumn' => 'column']],
            [['key'], ['column'], ['linked'], []],
        ];
    }
}
class EntityMapEntity extends Entity
{
    public $id;

    protected static function getMap()
    {
        return new EntityMap(
            '',
            ['id'],
            []
        );
    }
}