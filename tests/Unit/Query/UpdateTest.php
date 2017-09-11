<?php

namespace MadeSimple\Database\Tests\Unit\Query;

use MadeSimple\Database\Compiler;
use MadeSimple\Database\CompilerInterface;
use MadeSimple\Database\Query\Update;
use MadeSimple\Database\Tests\MockConnector;
use Psr\Log\NullLogger;
use MadeSimple\Database\Tests\MockConnection;
use MadeSimple\Database\Tests\TestCase;

class UpdateTest extends TestCase
{
    /**
     * @var MockConnection
     */
    private $mockConnection;

    /**
     * @var \Mockery\Mock|\PDO
     */
    private $mockPdo;

    /**
     * @var \Mockery\Mock|\PDOStatement
     */
    private $mockPdoStatement;

    /**
     * @var MockConnector
     */
    private $mockConnector;

    /**
     * @var CompilerInterface
     */
    private $compiler;

    protected function setUp()
    {
        parent::setUp();

        $this->mockPdo          = \Mockery::mock(\PDO::class);
        $this->mockPdoStatement = \Mockery::mock(\PDOStatement::class);
        $this->mockConnector    = new MockConnector($this->mockPdo);
        $this->compiler         = new Compiler\MySQL();
        $this->mockConnection   = new MockConnection($this->mockConnector, $this->compiler);
    }


    /**
     * Test update table.
     */
    public function testTable()
    {
        $sql    = 'UPDATE `table` SET `foo`=?';
        $update = (new Update($this->mockConnection))->table('table')->set('foo', 5);

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update set.
     */
    public function testSet()
    {
        $sql    = 'UPDATE `table` SET `foo`=?,`bar`=?';
        $update = (new Update($this->mockConnection))->table('table')->set('foo', 1)->set('bar', 2);

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update single column.
     */
    public function testColumnsSingle()
    {
        $sql    = 'UPDATE `table` SET `foo`=?';
        $update = (new Update($this->mockConnection))->table('table')->columns(['foo' => 5]);

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update multiple columns.
     */
    public function testColumnsMultiple()
    {
        $sql    = 'UPDATE `table` SET `foo`=?,`bar`=?';
        $update = (new Update($this->mockConnection))->table('table')->columns(['foo' => 5, 'bar' => 6]);

        $this->assertEquals($sql, $update->toSql());
    }



    /**
     * Test where.
     */
    public function testWhere()
    {
        $sql    = 'UPDATE `table` SET `field`=? WHERE `foo` = ?';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('foo', '=', 1);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where comparison operators.
     */
    public function testWhereComparisonOperators()
    {
        $sql    = 'UPDATE `table` SET `field`=? WHERE `field1` = ? AND `field2` > ? AND `field3` < ? AND `field4` >= ? AND `field5` <= ? AND `field6` <> ?';
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
    public function testWhereBetween()
    {
        $sql    = 'UPDATE `table` SET `field`=? WHERE `field` BETWEEN ? AND ?';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('field', 'between', [1, 9]);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where in.
     */
    public function testWhereIn()
    {
        $sql    = 'UPDATE `table` SET `field`=? WHERE `field` IN (?,?,?,?,?)';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('field', 'in', [1, 2, 3, 4, 5]);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with closure.
     */
    public function testWhereClosure()
    {
        $sql    = 'UPDATE `table` SET `field`=? WHERE ((`foo` != ? OR `bar` IN (?,?,?)) AND `baz` = ?)';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where(function ($query) {
                $query
                    ->where(function ($query) {
                        $query->where('foo', '!=', 5)->orWhere('bar', 'in', [1,2,3]);
                    })
                    ->where('baz', '=', 3);
            });
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test multiple wheres with AND boolean.
     */
    public function testAndWhere()
    {
        $sql    = 'UPDATE `table` SET `field`=? WHERE `foo` = ? AND `bar` = ?';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('foo', '=', 1)
            ->where('bar', '=', 2);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test multiple wheres with AND boolean.
     */
    public function testOrWhere()
    {
        $sql    = 'UPDATE `table` SET `field`=? WHERE `foo` = ? OR `bar` = ?';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('foo', '=', 1)
            ->orWhere('bar', '=', 2);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with a raw value.
     */
    public function testWhereRaw()
    {
        $sql    = 'UPDATE `table` SET `field`=? WHERE `foo` = ? AND `bar` = COUNT(`qux`)';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('foo', '=', 5)
            ->whereRaw('bar', '=', 'COUNT(`qux`)');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test or where with a raw value.
     */
    public function testOrWhereRaw()
    {
        $sql    = 'UPDATE `table` SET `field`=? WHERE `foo` = ? OR `bar` = COUNT(`qux`)';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('foo', '=', 5)
            ->orWhereRaw('bar', '=', 'COUNT(`qux`)');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where comparison of two columns.
     */
    public function testWhereColumn()
    {
        $sql    = 'UPDATE `table` SET `field`=? WHERE `foo` = ? AND `bar` = `qux`';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('foo', '=', 5)
            ->whereColumn('bar', '=', 'qux');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test or where comparison of two columns.
     */
    public function testOrWhereColumn()
    {
        $sql    = 'UPDATE `table` SET `field`=? WHERE `foo` = ? OR `bar` = `qux`';
        $select = (new Update($this->mockConnection))->table('table')
            ->set('field', 5)
            ->where('foo', '=', 5)
            ->orWhereColumn('bar', '=', 'qux');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where exists.
     */
    public function testWhereExists()
    {
        $sql    = 'UPDATE `table1` SET `field`=? WHERE EXISTS (SELECT * FROM `table2` WHERE `table1`.`id` = `table2`.`table1_id`)';
        $select = (new Update($this->mockConnection))->table('table1')
            ->set('field', 5)
            ->whereExists(function ($select) {
                $select->from('table2')->whereColumn('table1.id', '=', 'table2.table1_id');
            });
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where not exists.
     */
    public function testWhereNotExists()
    {
        $sql    = 'UPDATE `table1` SET `field`=? WHERE NOT EXISTS (SELECT * FROM `table2` WHERE `table1`.`id` = `table2`.`table1_id`)';
        $select = (new Update($this->mockConnection))->table('table1')
            ->set('field', 5)
            ->whereNotExists(function ($select) {
                $select->from('table2')->whereColumn('table1.id', '=', 'table2.table1_id');
            });
        $this->assertEquals($sql, $select->toSql());
    }
}