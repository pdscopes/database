<?php

namespace Tests\Unit\QueryBuilder;

use MadeSimple\Database\QueryBuilder\Clause;
use MadeSimple\Database\QueryBuilder\Select;
use Tests\MockConnection;
use Tests\TestCase;

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

    protected function setUp()
    {
        parent::setUp();

        $this->mockPdo          = \Mockery::mock(\PDO::class);
        $this->mockPdoStatement = \Mockery::mock(\PDOStatement::class);
        $this->mockConnection   = new MockConnection($this->mockPdo);
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
        $sql    = "SELECT `id`\nFROM `table`";
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
        $sql    = "SELECT *,`id`\nFROM `table`";
        $select = (new Select($this->mockConnection))->addColumns($columns)->from('table');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set from without an alias.
     */
    public function testFromWithoutAlias()
    {
        $sql    = "SELECT *\nFROM `table`";
        $select = (new Select($this->mockConnection))->from('old')->from('table');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set from with an alias.
     */
    public function testFromWithAlias()
    {
        $sql    = "SELECT *\nFROM `table` AS `t`";
        $select = (new Select($this->mockConnection))->from('old')->from('table', 't');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test add from without an alias.
     */
    public function testAddFromWithoutAlias()
    {
        $sql    = "SELECT *\nFROM `table1`,`table2`";
        $select = (new Select($this->mockConnection))->from('table1')->addFrom('table2');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test add from with an alias.
     */
    public function testAddFromWithAlias()
    {
        $sql    = "SELECT *\nFROM `table1`,`table2` AS `t2`";
        $select = (new Select($this->mockConnection))->from('table1')->addFrom('table2', 't2');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test join without an alias.
     */
    public function testJoinWithoutAlias()
    {
        $sql    = "SELECT *\nFROM `table`\nJOIN `join` ON `join`.`tableId` = `table`.`id`";
        $select = (new Select($this->mockConnection))->from('table')->join('join', 'join.tableId = table.id');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test join without an alias.
     */
    public function testJoinWithAlias()
    {
        $sql    = "SELECT *\nFROM `table`\nJOIN `join` AS `j` ON `j`.`tableId` = `table`.`id`";
        $select = (new Select($this->mockConnection))->from('table')->join('join', 'j.tableId = table.id', 'j');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test left join.
     */
    public function testLeftJoin()
    {
        $sql    = "SELECT *\nFROM `table`\nLEFT JOIN `join` ON `join`.`tableId` = `table`.`id`";
        $select = (new Select($this->mockConnection))->from('table')->leftJoin('join', 'join.tableId = table.id');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test right join.
     */
    public function testRightJoin()
    {
        $sql    = "SELECT *\nFROM `table`\nRIGHT JOIN `join` ON `join`.`tableId` = `table`.`id`";
        $select = (new Select($this->mockConnection))->from('table')->rightJoin('join', 'join.tableId = table.id');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test full join.
     */
    public function testFullJoin()
    {
        $sql    = "SELECT *\nFROM `table`\nFULL JOIN `join` ON `join`.`tableId` = `table`.`id`";
        $select = (new Select($this->mockConnection))->from('table')->fullJoin('join', 'join.tableId = table.id');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test inner join.
     */
    public function testInnerJoin()
    {
        $sql    = "SELECT *\nFROM `table`\nINNER JOIN `join` ON `join`.`tableId` = `table`.`id`";
        $select = (new Select($this->mockConnection))->from('table')->innerJoin('join', 'join.tableId = table.id');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set parameter.
     */
    public function testSetParameter()
    {
        $sql = "SELECT *\nFROM `table`\nWHERE `foo` = :bar";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':bar', 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $select = (new Select($this->mockConnection))->from('table')->where('foo = :bar')->setParameter('bar', 1);
        $this->assertEquals($this->mockPdoStatement, $select->execute());
    }

    /**
     * Test set wildcard parameters.
     */
    public function testSetParametersWildcard()
    {
        $sql = "SELECT *\nFROM `table`\nWHERE `foo` = ?";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $select = (new Select($this->mockConnection))->from('table')->where('foo = ?')->setParameters([1]);
        $this->assertEquals($this->mockPdoStatement, $select->execute());
    }

    /**
     * Test set named parameters.
     */
    public function testSetParametersNamed()
    {
        $sql = "SELECT *\nFROM `table`\nWHERE `foo` = :bar";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':bar', 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $select = (new Select($this->mockConnection))->from('table')->where('foo = :bar')->setParameters(['bar' => 1]);
        $this->assertEquals($this->mockPdoStatement, $select->execute());
    }

    /**
     * Test where without parameters
     */
    public function testWhereWithoutParameters()
    {
        $sql    = "SELECT *\nFROM `table`\nWHERE `foo` = 1";
        $select = (new Select($this->mockConnection))->from('table')->where('foo = 1');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with a wildcard parameter
     */
    public function testWhereWithWildcardParameter()
    {
        $sql    = "SELECT *\nFROM `table`\nWHERE `foo` = ?";
        $select = (new Select($this->mockConnection))->from('table')->where('foo = ?');

        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with a named parameter
     */
    public function testWhereWithNamedParameter()
    {
        $sql    = "SELECT *\nFROM `table`\nWHERE `foo` = :bar";
        $select = (new Select($this->mockConnection))->from('table')->where('foo = :bar');

        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with closure.
     */
    public function testWhereWithClosure()
    {
        $sql = "SELECT *\nFROM `table`\nWHERE ((`foo` = :foo OR `bar` = :bar) AND `baz` = :qux)";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':foo', 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':bar', 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':qux', 5, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $select = (new Select($this->mockConnection))
            ->from('table')
            ->where(function (Clause $clause) {
                return $clause
                    ->where(function (Clause $clause) {
                        return $clause
                            ->where('foo = :foo')
                            ->orX('bar = :bar');

                    }
                    )
                    ->andX('baz = :qux');
            }
            )
            ->setParameters([
                'foo' => 2,
                'bar' => 3,
                'qux' => 5,
            ]
            );

        $this->assertEquals($sql, $select->toSql());
        $this->assertEquals($this->mockPdoStatement, $select->execute());
    }

    /**
     * Test and where with no parameter, wildcard parameter, and named parameter.
     */
    public function testAndWhere()
    {
        $sql    = "SELECT *\nFROM `table`\nWHERE `foo` IS TRUE AND `bar` = ? AND (`baz` = :qux)";
        $select = (new Select($this->mockConnection))
            ->from('table')
            ->where('foo IS TRUE')
            ->andWhere('bar = ?')
            ->andWhere(function (Clause $clause) {
                return $clause->where('baz = :qux');
            }
            );

        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test or where with no parameter, wildcard parameter, and named parameter.
     */
    public function testOrWhere()
    {
        $sql    = "SELECT *\nFROM `table`\nWHERE `foo` IS NULL OR `bar` = ? OR (`baz` = :qux)";
        $select = (new Select($this->mockConnection))
            ->from('table')
            ->where('foo IS NULL')
            ->orWhere('bar = ?')
            ->orWhere(function (Clause $clause) {
                return $clause->where('baz = :qux');
            }
            );
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set group by.
     *
     * @param mixed $clauses
     *
     * @dataProvider groupByDataProvider
     */
    public function testGroupBy($clauses)
    {
        $sql    = "SELECT *\nFROM `table`\nGROUP BY `id`";
        $select = (new Select($this->mockConnection))->from('table')->groupBy($clauses);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test add group by.
     *
     * @param mixed $clauses
     *
     * @dataProvider groupByDataProvider
     */
    public function testAddGroupBy($clauses)
    {
        $sql    = "SELECT *\nFROM `table`\nGROUP BY `foo`,`id`";
        $select = (new Select($this->mockConnection))->from('table')->groupBy('foo')->addGroupBy($clauses);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set order by.
     *
     * @param mixed $clauses
     *
     * @dataProvider orderByDataProvider
     */
    public function testOrderBy($clauses)
    {
        $sql    = "SELECT *\nFROM `table`\nORDER BY `id` ASC";
        $select = (new Select($this->mockConnection))->from('table')->orderBy($clauses);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test add order by.
     *
     * @param mixed $clauses
     *
     * @dataProvider orderByDataProvider
     */
    public function testAddOrderBy($clauses)
    {
        $sql    = "SELECT *\nFROM `table`\nORDER BY `foo`,`id` ASC";
        $select = (new Select($this->mockConnection))->from('table')->orderBy('foo')->addOrderBy($clauses);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set limit by range.
     */
    public function testLimitRange()
    {
        $sql    = "SELECT *\nFROM `table`\nLIMIT 15";
        $select = (new Select($this->mockConnection))->from('table')->limit(15);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set limit be range and start
     */
    public function testLimitRangeAndStart()
    {
        $sql    = "SELECT *\nFROM `table`\nLIMIT 5, 15";
        $select = (new Select($this->mockConnection))->from('table')->limit(15, 5);
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

    public function orderByDataProvider()
    {
        return [
            [['id ASC']],
            ['id ASC'],
        ];
    }
}
