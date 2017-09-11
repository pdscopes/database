<?php

namespace MadeSimple\Database\Tests\Unit\Query;

use MadeSimple\Database\Compiler;
use MadeSimple\Database\CompilerInterface;
use MadeSimple\Database\Query\Select;
use MadeSimple\Database\Tests\MockConnector;
use Psr\Log\NullLogger;
use MadeSimple\Database\Tests\MockConnection;
use MadeSimple\Database\Tests\TestCase;

class SelectTest extends TestCase
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
     * Test set columns.
     *
     * @param mixed $columns
     *
     * @dataProvider columnsDataProvider
     */
    public function testColumns($columns)
    {
        $sql    = 'SELECT `id` FROM `table`';
        $select = (new Select($this->mockConnection))->columns($columns)->from('table');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test add columns.
     *
     * @param mixed $columns
     *
     * @dataProvider columnsDataProvider
     */
    public function testAddColumns($columns)
    {
        $sql    = 'SELECT *,`id` FROM `table`';
        $select = (new Select($this->mockConnection))->columns('*')->addColumns($columns)->from('table');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set from without an alias.
     */
    public function testFromWithoutAlias()
    {
        $sql    = 'SELECT * FROM `table`';
        $select = (new Select($this->mockConnection))->from('old')->from('table');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set from with an alias.
     */
    public function testFromWithAlias()
    {
        $sql    = 'SELECT * FROM `table` AS `t`';
        $select = (new Select($this->mockConnection))->from('old')->from('table', 't');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test add from without an alias.
     */
    public function testAddFromWithoutAlias()
    {
        $sql    = 'SELECT * FROM `table1`,`table2`';
        $select = (new Select($this->mockConnection))->from('table1')->addFrom('table2');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test add from with an alias.
     */
    public function testAddFromWithAlias()
    {
        $sql    = 'SELECT * FROM `table1`,`table2` AS `t2`';
        $select = (new Select($this->mockConnection))->from('table1')->addFrom('table2', 't2');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test join without an alias.
     */
    public function testJoinWithoutAlias()
    {
        $sql    = 'SELECT * FROM `table` INNER JOIN `join` ON `join`.`tableId` = `table`.`id`';
        $select = (new Select($this->mockConnection))->from('table')->join('join', 'join.tableId', '=', 'table.id');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test join without an alias.
     */
    public function testJoinWithAlias()
    {
        $sql    = 'SELECT * FROM `table` INNER JOIN `join` AS `j` ON `j`.`tableId` = `table`.`id`';
        $select = (new Select($this->mockConnection))->from('table')->join('join', 'j.tableId', '=', 'table.id', 'j');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test left join.
     */
    public function testLeftJoin()
    {
        $sql    = 'SELECT * FROM `table` LEFT JOIN `join` ON `join`.`tableId` = `table`.`id`';
        $select = (new Select($this->mockConnection))->from('table')->leftJoin('join', 'join.tableId', '=', 'table.id');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test right join.
     */
    public function testRightJoin()
    {
        $sql    = 'SELECT * FROM `table` RIGHT JOIN `join` ON `join`.`tableId` = `table`.`id`';
        $select = (new Select($this->mockConnection))->from('table')->rightJoin('join', 'join.tableId', '=', 'table.id');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where.
     */
    public function testWhere()
    {
        $sql    = 'SELECT * FROM `table` WHERE `foo` = ?';
        $select = (new Select($this->mockConnection))->from('table')->where('foo', '=', 1);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where comparison operators.
     */
    public function testWhereComparisonOperators()
    {
        $sql    = 'SELECT * FROM `table` WHERE `field1` = ? AND `field2` > ? AND `field3` < ? AND `field4` >= ? AND `field5` <= ? AND `field6` <> ?';
        $select = (new Select($this->mockConnection))
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
    public function testWhereBetween()
    {
        $sql    = 'SELECT * FROM `table` WHERE `field` BETWEEN ? AND ?';
        $select = (new Select($this->mockConnection))->from('table')
            ->where('field', 'between', [1, 9]);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where in.
     */
    public function testWhereIn()
    {
        $sql    = 'SELECT * FROM `table` WHERE `field` IN (?,?,?,?,?)';
        $select = (new Select($this->mockConnection))->from('table')
            ->where('field', 'in', [1, 2, 3, 4, 5]);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with closure.
     */
    public function testWhereClosure()
    {
        $sql    = 'SELECT * FROM `table` WHERE ((`foo` != ? OR `bar` IN (?,?,?)) AND `baz` = ?)';
        $select = (new Select($this->mockConnection))->from('table')
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
        $sql    = 'SELECT * FROM `table` WHERE `foo` = ? AND `bar` = ?';
        $select = (new Select($this->mockConnection))->from('table')
            ->where('foo', '=', 1)
            ->where('bar', '=', 2);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test multiple wheres with AND boolean.
     */
    public function testOrWhere()
    {
        $sql    = 'SELECT * FROM `table` WHERE `foo` = ? OR `bar` = ?';
        $select = (new Select($this->mockConnection))->from('table')
            ->where('foo', '=', 1)
            ->orWhere('bar', '=', 2);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with a raw value.
     */
    public function testWhereRaw()
    {
        $sql    = 'SELECT * FROM `table` WHERE `foo` = ? AND `bar` = COUNT(`qux`)';
        $select = (new Select($this->mockConnection))->from('table')
            ->where('foo', '=', 5)
            ->whereRaw('bar', '=', 'COUNT(`qux`)');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test or where with a raw value.
     */
    public function testOrWhereRaw()
    {
        $sql    = 'SELECT * FROM `table` WHERE `foo` = ? OR `bar` = COUNT(`qux`)';
        $select = (new Select($this->mockConnection))->from('table')
            ->where('foo', '=', 5)
            ->orWhereRaw('bar', '=', 'COUNT(`qux`)');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where comparison of two columns.
     */
    public function testWhereColumn()
    {
        $sql    = 'SELECT * FROM `table` WHERE `foo` = ? AND `bar` = `qux`';
        $select = (new Select($this->mockConnection))->from('table')
            ->where('foo', '=', 5)
            ->whereColumn('bar', '=', 'qux');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test or where comparison of two columns.
     */
    public function testOrWhereColumn()
    {
        $sql    = 'SELECT * FROM `table` WHERE `foo` = ? OR `bar` = `qux`';
        $select = (new Select($this->mockConnection))->from('table')
            ->where('foo', '=', 5)
            ->orWhereColumn('bar', '=', 'qux');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where exists.
     */
    public function testWhereExists()
    {
        $sql    = 'SELECT * FROM `table1` WHERE EXISTS (SELECT * FROM `table2` WHERE `table1`.`id` = `table2`.`table1_id`)';
        $select = (new Select($this->mockConnection))->from('table1')
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
        $sql    = 'SELECT * FROM `table1` WHERE NOT EXISTS (SELECT * FROM `table2` WHERE `table1`.`id` = `table2`.`table1_id`)';
        $select = (new Select($this->mockConnection))->from('table1')
            ->whereNotExists(function ($select) {
                $select->from('table2')->whereColumn('table1.id', '=', 'table2.table1_id');
            });
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set group by.
     *
     * @param mixed $clauses
     *
     * @dataProvider groupByDataProvider
     */
    public function testGroupBySingle($clauses)
    {
        $sql    = 'SELECT * FROM `table` GROUP BY `id`';
        $select = (new Select($this->mockConnection))->from('table')->groupBy($clauses);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test add group by.
     *
     * @param mixed $columns
     *
     * @dataProvider groupByDataProvider
     */
    public function testGroupByMultiple($columns)
    {
        $sql    = 'SELECT * FROM `table` GROUP BY `foo`,`id`';
        $select = (new Select($this->mockConnection))->from('table')->groupBy('foo')->groupBy($columns);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set order by without direction.
     */
    public function testOrderByWithoutDirection()
    {
        $sql    = 'SELECT * FROM `table` ORDER BY `id` ASC';
        $select = (new Select($this->mockConnection))->from('table')->orderBy('id');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set order by with direction.
     */
    public function testOrderByWithDirection()
    {
        $sql    = 'SELECT * FROM `table` ORDER BY `id` DESC';
        $select = (new Select($this->mockConnection))->from('table')->orderBy('id', 'desc');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set order by with null direction.
     */
    public function testOrderByWithNullDirection()
    {
        $sql    = 'SELECT * FROM `table` ORDER BY `id`';
        $select = (new Select($this->mockConnection))->from('table')->orderBy('id', null);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set limit.
     */
    public function testLimit()
    {
        $sql    = 'SELECT * FROM `table` LIMIT 15';
        $select = (new Select($this->mockConnection))->from('table')->limit(15);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set offset.
     */
    public function testOffset()
    {
        $sql    = 'SELECT * FROM `table` OFFSET 5';
        $select = (new Select($this->mockConnection))->from('table')->offset(5);
        $this->assertEquals($sql, $select->toSql());
    }


    public function columnsDataProvider()
    {
        return [
            [['id']],
            ['id'],
        ];
    }

    public function groupByDataProvider()
    {
        return [
            [['id']],
            ['id'],
        ];
    }
}
