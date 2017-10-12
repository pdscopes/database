<?php

namespace MadeSimple\Database\Tests\Integration\Compiler;

use MadeSimple\Database\Query\Select;
use MadeSimple\Database\Query\Update;
use MadeSimple\Database\Query\WhereBuilder;
use MadeSimple\Database\Tests\CompilableSQLiteTestCase;

class SQLiteQueryUpdateTest extends CompilableSQLiteTestCase
{
    /**
     * Test update table.
     */
    public function testQueryUpdateTable()
    {
        $sql    = 'UPDATE "table" SET "foo" = ?';
        $update = (new Update($this->mockConnection))->table('table')->set('foo', 5);

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update set.
     */
    public function testQueryUpdateSet()
    {
        $sql    = 'UPDATE "table" SET "foo" = ?, "bar" = ?';
        $update = (new Update($this->mockConnection))->table('table')->set('foo', 1)->set('bar', 2);

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update set array single column.
     */
    public function testQueryUpdateSetArraySingle()
    {
        $sql    = 'UPDATE "table" SET "foo" = ?';
        $update = (new Update($this->mockConnection))->table('table')->set(['foo' => 5]);

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update set array multiple columns.
     */
    public function testQueryUpdateSetArrayMultiple()
    {
        $sql    = 'UPDATE "table" SET "foo" = ?, "bar" = ?';
        $update = (new Update($this->mockConnection))->table('table')->set(['foo' => 5, 'bar' => 6]);

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update set when value is a column.
     */
    public function testQueryUpdateSetColumn()
    {
        $sql    = 'UPDATE "table" SET "foo" = "bar"';
        $update = (new Update($this->mockConnection))->table('table')->setColumn('foo', 'bar');

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update set when value is a raw.
     */
    public function testQueryUpdateSetRaw()
    {
        $sql    = 'UPDATE "table" SET "foo" = "bar" + 1';
        $update = (new Update($this->mockConnection))->table('table')->setRaw('foo', '"bar" + 1');

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update set when value is an object.
     */
    public function testQueryUpdateSetDateTime()
    {
        $sql    = 'UPDATE "table" SET "foo" = ?';
        $update = (new Update($this->mockConnection))->table('table')->set('foo', new \DateTime('2000-01-01 00:00:00'));

        list($builtSql, $bindings) = $update->buildSql();
        $this->assertEquals($sql, $builtSql);
        $this->assertEquals(['2000-01-01 00:00:00'], $bindings);
    }



    /**
     * Test where.
     */
    public function testQueryUpdateWhere()
    {
        $sql    = 'UPDATE "table" SET "field" = ? WHERE "foo" = ?';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('foo', '=', 1);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where comparison operators.
     */
    public function testQueryUpdateWhereComparisonOperators()
    {
        $sql    = 'UPDATE "table" SET "field" = ? WHERE "field1" = ? AND "field2" > ? AND "field3" < ? AND "field4" >= ? AND "field5" <= ? AND "field6" <> ?';
        $select = (new Update($this->mockConnection))
            ->table('table')
            ->set('field', 5)
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
    public function testQueryUpdateWhereBetween()
    {
        $sql    = 'UPDATE "table" SET "field" = ? WHERE "field" BETWEEN ? AND ?';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('field', 'between', [1, 9]);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where in.
     */
    public function testQueryUpdateWhereIn()
    {
        $sql    = 'UPDATE "table" SET "field" = ? WHERE "field" IN (?,?,?,?,?)';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('field', 'in', [1, 2, 3, 4, 5]);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with closure.
     */
    public function testQueryUpdateWhereClosure()
    {
        $sql    = 'UPDATE "table" SET "field" = ? WHERE (("foo" != ? OR "bar" IN (?,?,?)) AND "baz" = ?)';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
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
    public function testQueryUpdateAndWhere()
    {
        $sql    = 'UPDATE "table" SET "field" = ? WHERE "foo" = ? AND "bar" = ?';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('foo', '=', 1)
            ->where('bar', '=', 2);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test multiple wheres with AND boolean.
     */
    public function testQueryUpdateOrWhere()
    {
        $sql    = 'UPDATE "table" SET "field" = ? WHERE "foo" = ? OR "bar" = ?';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('foo', '=', 1)
            ->orWhere('bar', '=', 2);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with a raw value.
     */
    public function testQueryUpdateWhereRaw()
    {
        $sql    = 'UPDATE "table" SET "field" = ? WHERE "foo" = ? AND "bar" = COUNT("qux")';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('foo', '=', 5)
            ->whereRaw('bar', '=', 'COUNT("qux")');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test or where with a raw value.
     */
    public function testQueryUpdateOrWhereRaw()
    {
        $sql    = 'UPDATE "table" SET "field" = ? WHERE "foo" = ? OR "bar" = COUNT("qux")';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('foo', '=', 5)
            ->orWhereRaw('bar', '=', 'COUNT("qux")');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where comparison of two columns.
     */
    public function testQueryUpdateWhereColumn()
    {
        $sql    = 'UPDATE "table" SET "field" = ? WHERE "foo" = ? AND "bar" = "qux"';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('foo', '=', 5)
            ->whereColumn('bar', '=', 'qux');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test or where comparison of two columns.
     */
    public function testQueryUpdateOrWhereColumn()
    {
        $sql    = 'UPDATE "table" SET "field" = ? WHERE "foo" = ? OR "bar" = "qux"';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('foo', '=', 5)
            ->orWhereColumn('bar', '=', 'qux');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where exists.
     */
    public function testQueryUpdateWhereExists()
    {
        $sql    = 'UPDATE "table1" SET "field" = ? WHERE EXISTS (SELECT * FROM "table2" WHERE "table1"."id" = "table2"."table1_id")';
        $select = (new Update($this->mockConnection))->table('table1')
            ->set('field', 5)
            ->whereExists(function (Select $select) {
                $select->from('table2')->whereColumn('table1.id', '=', 'table2.table1_id');
            });
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where not exists.
     */
    public function testQueryUpdateWhereNotExists()
    {
        $sql    = 'UPDATE "table1" SET "field" = ? WHERE NOT EXISTS (SELECT * FROM "table2" WHERE "table1"."id" = "table2"."table1_id")';
        $select = (new Update($this->mockConnection))->table('table1')
            ->set('field', 5)
            ->whereNotExists(function (Select $select) {
                $select->from('table2')->whereColumn('table1.id', '=', 'table2.table1_id');
            });
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with value as a sub query.
     * @group failing
     */
    public function testQuerySelectWhereValueSubQuery()
    {
        $sql    = 'UPDATE "table1" SET "field" = ? WHERE "field" = (SELECT * FROM "table2" WHERE "table1"."id" = "table2"."table1_id")';
        $select = (new Update($this->mockConnection))->table('table1')
            ->set('field', 5)
            ->where('field', '=', function (Select $select) {
                $select->from('table2')->whereColumn('table1.id', '=', 'table2.table1_id');
            });
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where sub query.
     */
    public function testQuerySelectWhereSubQuery()
    {
        $sql    = 'UPDATE "table1" SET "field" = ? WHERE (SELECT * FROM "table2" WHERE "table1"."id" = "table2"."table1_id")';
        $select = (new Update($this->mockConnection))->table('table1')
            ->set('field', 5)
            ->whereSubQuery(function (Select $select) {
                $select->from('table2')->whereColumn('table1.id', '=', 'table2.table1_id');
            });
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test or where sub query.
     */
    public function testQuerySelectOrWhereSubQuery()
    {
        $sql    = 'UPDATE "table1" SET "field" = ? WHERE "field" = ? OR (SELECT * FROM "table2" WHERE "table1"."id" = "table2"."table1_id")';
        $select = (new Update($this->mockConnection))->table('table1')
            ->set('field', 5)
            ->where('field', '=', 'value')
            ->orWhereSubQuery(function (Select $select) {
                $select->from('table2')->whereColumn('table1.id', '=', 'table2.table1_id');
            });
        $this->assertEquals($sql, $select->toSql());
    }
}