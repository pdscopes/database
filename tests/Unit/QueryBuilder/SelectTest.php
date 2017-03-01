<?php

namespace Tests\Unit\QueryBuilder;

use MadeSimple\Database\QueryBuilder\Select;
use Tests\TestCase;

class SelectTest extends TestCase
{
    /**
     * @var \Mockery\Mock|\PDO
     */
    private $mockPdo;

    /**
     * @var \Mockery\Mock|\PDOStatement
     */
    private $mockPdoStatement;

    protected function setUp()
    {
        parent::setUp();

        $this->mockPdo          = \Mockery::mock(\PDO::class);
        $this->mockPdoStatement = \Mockery::mock(\PDOStatement::class);
    }


    /**
     * Test set columns.
     *
     * @param mixed $columns
     * @dataProvider columnsDataProvider
     */
    public function testColumns($columns)
    {
        $sql = (new Select($this->mockPdo))->columns($columns)->from('table')->toSql();
        $this->assertEquals("SELECT `id`\nFROM `table`", $sql);
    }

    /**
     * Test add columns.
     *
     * @param mixed $columns
     * @dataProvider columnsDataProvider
     */
    public function testAddColumns($columns)
    {
        $sql = (new Select($this->mockPdo))->addColumns($columns)->from('table')->toSql();
        $this->assertEquals("SELECT *,`id`\nFROM `table`", $sql);
    }

    /**
     * Test set from without an alias.
     */
    public function testFromWithoutAlias()
    {
        $sql = (new Select($this->mockPdo))->from('old')->from('table')->toSql();
        $this->assertEquals("SELECT *\nFROM `table`", $sql);
    }

    /**
     * Test set from with an alias.
     */
    public function testFromWithAlias()
    {
        $sql = (new Select($this->mockPdo))->from('old')->from('table', 't')->toSql();
        $this->assertEquals("SELECT *\nFROM `table` AS `t`", $sql);
    }

    /**
     * Test add from without an alias.
     */
    public function testAddFromWithoutAlias()
    {
        $sql = (new Select($this->mockPdo))->from('table1')->addFrom('table2')->toSql();
        $this->assertEquals("SELECT *\nFROM `table1`,`table2`", $sql);
    }

    /**
     * Test add from with an alias.
     */
    public function testAddFromWithAlias()
    {
        $sql = (new Select($this->mockPdo))->from('table1')->addFrom('table2', 't2')->toSql();
        $this->assertEquals("SELECT *\nFROM `table1`,`table2` AS `t2`", $sql);
    }

    /**
     * Test join without an alias.
     */
    public function testJoinWithoutAlias()
    {
        $sql = (new Select($this->mockPdo))->from('table')->join('join', 'join.tableId = table.id')->toSql();
        $this->assertEquals("SELECT *\nFROM `table`\nJOIN `join` ON `join`.`tableId` = `table`.`id`", $sql);
    }

    /**
     * Test join without an alias.
     */
    public function testJoinWithAlias()
    {
        $sql = (new Select($this->mockPdo))->from('table')->join('join', 'j.tableId = table.id', 'j')->toSql();
        $this->assertEquals("SELECT *\nFROM `table`\nJOIN `join` AS `j` ON `j`.`tableId` = `table`.`id`", $sql);
    }

    /**
     * Test left join.
     */
    public function testLeftJoin()
    {
        $sql = (new Select($this->mockPdo))->from('table')->leftJoin('join', 'join.tableId = table.id')->toSql();
        $this->assertEquals("SELECT *\nFROM `table`\nLEFT JOIN `join` ON `join`.`tableId` = `table`.`id`", $sql);
    }

    /**
     * Test right join.
     */
    public function testRightJoin()
    {
        $sql = (new Select($this->mockPdo))->from('table')->rightJoin('join', 'join.tableId = table.id')->toSql();
        $this->assertEquals("SELECT *\nFROM `table`\nRIGHT JOIN `join` ON `join`.`tableId` = `table`.`id`", $sql);
    }

    /**
     * Test full join.
     */
    public function testFullJoin()
    {
        $sql = (new Select($this->mockPdo))->from('table')->fullJoin('join', 'join.tableId = table.id')->toSql();
        $this->assertEquals("SELECT *\nFROM `table`\nFULL JOIN `join` ON `join`.`tableId` = `table`.`id`", $sql);
    }

    /**
     * Test inner join.
     */
    public function testInnerJoin()
    {
        $sql = (new Select($this->mockPdo))->from('table')->innerJoin('join', 'join.tableId = table.id')->toSql();
        $this->assertEquals("SELECT *\nFROM `table`\nINNER JOIN `join` ON `join`.`tableId` = `table`.`id`", $sql);
    }

    /**
     * Test where without parameters
     */
    public function testWhereWithoutParameters()
    {
        $sql = (new Select($this->mockPdo))->from('table')->where('foo = 1')->toSql();
        $this->assertEquals("SELECT *\nFROM `table`\nWHERE\n    `foo` = 1", $sql);
    }

    /**
     * Test where with a single wildcard parameter
     */
    public function testWhereWithSingleWildcardParameter()
    {
        $sql = "SELECT *\nFROM `table`\nWHERE\n    `foo` = ?";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql)->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $select = (new Select($this->mockPdo))->from('table')->where('foo = ?', 1);

        $this->assertEquals($sql, $select->toSql());
        $this->assertEquals($this->mockPdoStatement, $select->execute());
    }

    /**
     * Test where with multiple wildcard parameter
     */
    public function testWhereWithMultipleWildcardParameters()
    {
        $sql = "SELECT *\nFROM `table`\nWHERE\n    `foo` = ? AND `bar` = ?";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql)->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(2, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $select = (new Select($this->mockPdo))->from('table')->where('foo = ? AND bar = ?', [1, 2]);

        $this->assertEquals($sql, $select->toSql());
        $this->assertEquals($this->mockPdoStatement, $select->execute());
    }

    /**
     * Test where with a single named parameter
     */
    public function testWhereWithSingleNamedParameter()
    {
        $sql = "SELECT *\nFROM `table`\nWHERE\n    `foo` = :bar";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql)->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':bar', 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $select = (new Select($this->mockPdo))->from('table')->where('foo = :bar', ['bar' => 1]);

        $this->assertEquals($sql, $select->toSql());
        $this->assertEquals($this->mockPdoStatement, $select->execute());
    }

    /**
     * Test where with multiple named parameter
     */
    public function testWhereWithMultipleParameter()
    {
        $sql = "SELECT *\nFROM `table`\nWHERE\n    `foo` = :foo AND `bar` = :bar";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql)->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':foo', 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':bar', 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $select = (new Select($this->mockPdo))->from('table')->where('foo = :foo AND bar = :bar', ['foo' => 1, 'bar' => 2]);

        $this->assertEquals($sql, $select->toSql());
        $this->assertEquals($this->mockPdoStatement, $select->execute());
    }

    /**
     * Test or where without parameters.
     */
    public function testOrWhereWithoutParameters()
    {
        $sql = (new Select($this->mockPdo))->from('table')->orWhere(['foo = 1', 'bar = 2'])->toSql();
        $this->assertEquals("SELECT *\nFROM `table`\nWHERE\n    ((`foo` = 1) OR (`bar` = 2))", $sql);
    }

    /**
     * Test or where with wildcard parameters.
     */
    public function testOrWhereWithMultipleWildcardParameters()
    {
        $sql = "SELECT *\nFROM `table`\nWHERE\n    ((`foo` = ?) OR (`bar` = ?))";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql)->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(2, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $select = (new Select($this->mockPdo))->from('table')->orWhere(['foo = ?', 'bar = ?'], [1, 2]);

        $this->assertEquals($sql, $select->toSql());
        $this->assertEquals($this->mockPdoStatement, $select->execute());
    }

    /**
     * Test or where with named parameters.
     */
    public function testOrWhereWithMultipleNamedParameters()
    {
        $sql = "SELECT *\nFROM `table`\nWHERE\n    ((`foo` = :foo) OR (`bar` = :bar))";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql)->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':foo', 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':bar', 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $select = (new Select($this->mockPdo))->from('table')->orWhere(['foo = :foo', 'bar = :bar'], ['foo' => 1, 'bar' => 2]);

        $this->assertEquals($sql, $select->toSql());
        $this->assertEquals($this->mockPdoStatement, $select->execute());
    }

    /**
     * Test set group by.
     *
     * @param mixed $clauses
     * @dataProvider groupByDataProvider
     */
    public function testGroupBy($clauses)
    {
        $sql = (new Select($this->mockPdo))->from('table')->groupBy($clauses)->toSql();
        $this->assertEquals("SELECT *\nFROM `table`\nGROUP BY `id`", $sql);
    }

    /**
     * Test add group by.
     *
     * @param mixed $clauses
     * @dataProvider groupByDataProvider
     */
    public function testAddGroupBy($clauses)
    {
        $sql = (new Select($this->mockPdo))->from('table')->groupBy('foo')->addGroupBy($clauses)->toSql();
        $this->assertEquals("SELECT *\nFROM `table`\nGROUP BY `foo`,`id`", $sql);
    }

    /**
     * Test set order by.
     *
     * @param mixed $clauses
     * @dataProvider orderByDataProvider
     */
    public function testOrderBy($clauses)
    {
        $sql = (new Select($this->mockPdo))->from('table')->orderBy($clauses)->toSql();
        $this->assertEquals("SELECT *\nFROM `table`\nORDER BY `id` ASC", $sql);
    }

    /**
     * Test add order by.
     *
     * @param mixed $clauses
     * @dataProvider orderByDataProvider
     */
    public function testAddOrderBy($clauses)
    {
        $sql = (new Select($this->mockPdo))->from('table')->orderBy('foo')->addOrderBy($clauses)->toSql();
        $this->assertEquals("SELECT *\nFROM `table`\nORDER BY `foo`,`id` ASC", $sql);
    }

    /**
     * Test set limit by range.
     */
    public function testLimitRange()
    {
        $sql = (new Select($this->mockPdo))->from('table')->limit(15)->toSql();
        $this->assertEquals("SELECT *\nFROM `table`\nLIMIT 15", $sql);
    }

    /**
     * Test set limit be range and start
     */
    public function testLimitRangeAndStart()
    {
        $sql = (new Select($this->mockPdo))->from('table')->limit(15, 5)->toSql();
        $this->assertEquals("SELECT *\nFROM `table`\nLIMIT 5, 15", $sql);
    }


    public function columnsDataProvider()
    {
        return [
            [['id']],
            ['id']
        ];
    }
    public function groupByDataProvider()
    {
        return [
            [['id']],
            ['id']
        ];
    }
    public function orderByDataProvider()
    {
        return [
            [['id ASC']],
            ['id ASC']
        ];
    }
}
