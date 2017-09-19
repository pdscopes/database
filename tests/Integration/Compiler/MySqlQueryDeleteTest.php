<?php

namespace MadeSimple\Database\Tests\Integration\Compiler;

use MadeSimple\Database\Query\Delete;
use MadeSimple\Database\Query\Select;
use MadeSimple\Database\Query\WhereBuilder;
use MadeSimple\Database\Tests\CompilableMySqlTestCase;

class MySqlQueryDeleteTest extends CompilableMySqlTestCase
{
    /**
     * Test delete from without alias.
     */
    public function testQueryDeleteFromWithoutAlias()
    {
        $sql    = 'DELETE FROM `table`';
        $delete = (new Delete($this->mockConnection))->from('table');

        $this->assertEquals($sql, $delete->toSql());
    }

    /**
     * Test delete from with alias.
     */
    public function testQueryDeleteFromWithAlias()
    {
        $sql    = 'DELETE `t` FROM `table` AS `t`';
        $delete = (new Delete($this->mockConnection))->from('table', 't');

        $this->assertEquals($sql, $delete->toSql());
    }



    /**
     * Test where.
     */
    public function testQueryDeleteWhere()
    {
        $sql    = 'DELETE FROM `table` WHERE `foo` = ?';
        $select = (new Delete($this->mockConnection))->from('table')->where('foo', '=', 1);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where comparison operators.
     */
    public function testQueryDeleteWhereComparisonOperators()
    {
        $sql    = 'DELETE FROM `table` WHERE `field1` = ? AND `field2` > ? AND `field3` < ? AND `field4` >= ? AND `field5` <= ? AND `field6` <> ?';
        $select = (new Delete($this->mockConnection))
            ->from('table')
            ->where('field1', '=', 1)
            ->where('field2', '>', 1)
            ->where('field3', '<', 1)
            ->where('field4', '>=', 1)
            ->where('field5', '<=', 1)
            ->where('field6', '<>', 1);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where between.
     */
    public function testQueryDeleteWhereBetween()
    {
        $sql    = 'DELETE FROM `table` WHERE `field` BETWEEN ? AND ?';
        $select = (new Delete($this->mockConnection))->from('table')
            ->where('field', 'between', [1, 9]);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where in.
     */
    public function testQueryDeleteWhereIn()
    {
        $sql    = 'DELETE FROM `table` WHERE `field` IN (?,?,?,?,?)';
        $select = (new Delete($this->mockConnection))->from('table')
            ->where('field', 'in', [1, 2, 3, 4, 5]);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with closure.
     */
    public function testQueryDeleteWhereClosure()
    {
        $sql    = 'DELETE FROM `table` WHERE ((`foo` != ? OR `bar` IN (?,?,?)) AND `baz` = ?)';
        $select = (new Delete($this->mockConnection))->from('table')
            ->where(function (WhereBuilder $query) {
                $query
                    ->where(function (WhereBuilder $query) {
                        $query->where('foo', '!=', 5)->orWhere('bar', 'in', [1,2,3]);
                    })
                    ->where('baz', '=', 3);
            });
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test multiple wheres with AND boolean.
     */
    public function testQueryDeleteAndWhere()
    {
        $sql    = 'DELETE FROM `table` WHERE `foo` = ? AND `bar` = ?';
        $select = (new Delete($this->mockConnection))->from('table')
            ->where('foo', '=', 1)
            ->where('bar', '=', 2);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test multiple wheres with AND boolean.
     */
    public function testQueryDeleteOrWhere()
    {
        $sql    = 'DELETE FROM `table` WHERE `foo` = ? OR `bar` = ?';
        $select = (new Delete($this->mockConnection))->from('table')
            ->where('foo', '=', 1)
            ->orWhere('bar', '=', 2);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with a raw value.
     */
    public function testQueryDeleteWhereRaw()
    {
        $sql    = 'DELETE FROM `table` WHERE `foo` = ? AND `bar` = COUNT(`qux`)';
        $select = (new Delete($this->mockConnection))->from('table')
            ->where('foo', '=', 5)
            ->whereRaw('bar', '=', 'COUNT(`qux`)');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test or where with a raw value.
     */
    public function testQueryDeleteOrWhereRaw()
    {
        $sql    = 'DELETE FROM `table` WHERE `foo` = ? OR `bar` = COUNT(`qux`)';
        $select = (new Delete($this->mockConnection))->from('table')
            ->where('foo', '=', 5)
            ->orWhereRaw('bar', '=', 'COUNT(`qux`)');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where comparison of two columns.
     */
    public function testQueryDeleteWhereColumn()
    {
        $sql    = 'DELETE FROM `table` WHERE `foo` = ? AND `bar` = `qux`';
        $select = (new Delete($this->mockConnection))->from('table')
            ->where('foo', '=', 5)
            ->whereColumn('bar', '=', 'qux');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test or where comparison of two columns.
     */
    public function testQueryDeleteOrWhereColumn()
    {
        $sql    = 'DELETE FROM `table` WHERE `foo` = ? OR `bar` = `qux`';
        $select = (new Delete($this->mockConnection))->from('table')
            ->where('foo', '=', 5)
            ->orWhereColumn('bar', '=', 'qux');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where exists.
     */
    public function testQueryDeleteWhereExists()
    {
        $sql    = 'DELETE FROM `table1` WHERE EXISTS (SELECT * FROM `table2` WHERE `table1`.`id` = `table2`.`table1_id`)';
        $select = (new Delete($this->mockConnection))->from('table1')
            ->whereExists(function (Select $select) {
                $select->from('table2')->whereColumn('table1.id', '=', 'table2.table1_id');
            });
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where not exists.
     */
    public function testQueryDeleteWhereNotExists()
    {
        $sql    = 'DELETE FROM `table1` WHERE NOT EXISTS (SELECT * FROM `table2` WHERE `table1`.`id` = `table2`.`table1_id`)';
        $select = (new Delete($this->mockConnection))->from('table1')
            ->whereNotExists(function (Select $select) {
                $select->from('table2')->whereColumn('table1.id', '=', 'table2.table1_id');
            });
        $this->assertEquals($sql, $select->toSql());
    }
}