<?php

namespace Tests\Unit\Query;

use MadeSimple\Database\Statement\Query\Clause;
use MadeSimple\Database\Statement\Query\Select;
use Psr\Log\NullLogger;
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
        $sql    = 'SELECT `id` FROM `table`';
        $select = (new Select($this->mockConnection, new NullLogger))->columns($columns)->from('table');
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
        $select = (new Select($this->mockConnection, new NullLogger))->addColumns($columns)->from('table');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set from without an alias.
     */
    public function testFromWithoutAlias()
    {
        $sql    = 'SELECT * FROM `table`';
        $select = (new Select($this->mockConnection, new NullLogger))->from('old')->from('table');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set from with an alias.
     */
    public function testFromWithAlias()
    {
        $sql    = 'SELECT * FROM `table` AS `t`';
        $select = (new Select($this->mockConnection, new NullLogger))->from('old')->from('table', 't');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test add from without an alias.
     */
    public function testAddFromWithoutAlias()
    {
        $sql    = 'SELECT * FROM `table1`,`table2`';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table1')->addFrom('table2');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test add from with an alias.
     */
    public function testAddFromWithAlias()
    {
        $sql    = 'SELECT * FROM `table1`,`table2` AS `t2`';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table1')->addFrom('table2', 't2');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test join without an alias.
     */
    public function testJoinWithoutAlias()
    {
        $sql    = 'SELECT * FROM `table` JOIN `join` ON `join`.`tableId` = `table`.`id`';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->join('join', 'join.tableId = table.id');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test join without an alias.
     */
    public function testJoinWithAlias()
    {
        $sql    = 'SELECT * FROM `table` JOIN `join` AS `j` ON `j`.`tableId` = `table`.`id`';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->join('join', 'j.tableId = table.id', 'j');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test left join.
     */
    public function testLeftJoin()
    {
        $sql    = 'SELECT * FROM `table` LEFT JOIN `join` ON `join`.`tableId` = `table`.`id`';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->leftJoin('join', 'join.tableId = table.id');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test right join.
     */
    public function testRightJoin()
    {
        $sql    = 'SELECT * FROM `table` RIGHT JOIN `join` ON `join`.`tableId` = `table`.`id`';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->rightJoin('join', 'join.tableId = table.id');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test full join.
     */
    public function testFullJoin()
    {
        $sql    = 'SELECT * FROM `table` FULL JOIN `join` ON `join`.`tableId` = `table`.`id`';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->fullJoin('join', 'join.tableId = table.id');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test inner join.
     */
    public function testInnerJoin()
    {
        $sql    = 'SELECT * FROM `table` INNER JOIN `join` ON `join`.`tableId` = `table`.`id`';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->innerJoin('join', 'join.tableId = table.id');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set parameter.
     */
    public function testSetParameter()
    {
        $sql = 'SELECT * FROM `table` WHERE `foo` = :bar';

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':bar', 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->where('foo = :bar')->setParameter('bar', 1);
        $this->assertEquals($this->mockPdoStatement, $select->execute());
    }

    /**
     * Test set wildcard parameters.
     */
    public function testSetParametersWildcard()
    {
        $sql = 'SELECT * FROM `table` WHERE `foo` = ?';

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->where('foo = ?')->setParameters([1]);
        $this->assertEquals($this->mockPdoStatement, $select->execute());
    }

    /**
     * Test set named parameters.
     */
    public function testSetParametersNamed()
    {
        $sql = 'SELECT * FROM `table` WHERE `foo` = :bar';

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':bar', 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->where('foo = :bar')->setParameters(['bar' => 1]);
        $this->assertEquals($this->mockPdoStatement, $select->execute());
    }

    /**
     * Test where without parameters
     */
    public function testWhereWithoutParameters()
    {
        $sql    = 'SELECT * FROM `table` WHERE `foo` = 1';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->where('foo = 1');
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with a wildcard parameter
     */
    public function testWhereWithWildcardParameter()
    {
        $sql    = 'SELECT * FROM `table` WHERE `foo` = ?';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->where('foo = ?');

        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with a named parameter
     */
    public function testWhereWithNamedParameter()
    {
        $sql    = 'SELECT * FROM `table` WHERE `foo` = :bar';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->where('foo = :bar');

        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test where with closure.
     */
    public function testWhereWithClosure()
    {
        $sql = 'SELECT * FROM `table` WHERE ((`foo` = :foo OR `bar` = :bar) AND `baz` = :qux)';

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':foo', 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':bar', 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':qux', 5, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $select = (new Select($this->mockConnection, new NullLogger))
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
        $sql    = 'SELECT * FROM `table` WHERE `foo` IS TRUE AND `bar` = ? AND (`baz` = :qux)';
        $select = (new Select($this->mockConnection, new NullLogger))
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
        $sql    = 'SELECT * FROM `table` WHERE `foo` IS NULL OR `bar` = ? OR (`baz` = :qux)';
        $select = (new Select($this->mockConnection, new NullLogger))
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
        $sql    = 'SELECT * FROM `table` GROUP BY `id`';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->groupBy($clauses);
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
        $sql    = 'SELECT * FROM `table` GROUP BY `foo`,`id`';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->groupBy('foo')->addGroupBy($clauses);
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
        $sql    = 'SELECT * FROM `table` ORDER BY `id` ASC';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->orderBy($clauses);
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
        $sql    = 'SELECT * FROM `table` ORDER BY `foo`,`id` ASC';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->orderBy('foo')->addOrderBy($clauses);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set limit by range.
     */
    public function testLimitRange()
    {
        $sql    = 'SELECT * FROM `table` LIMIT 15';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->limit(15);
        $this->assertEquals($sql, $select->toSql());
    }

    /**
     * Test set limit be range and start
     */
    public function testLimitRangeAndStart()
    {
        $sql    = 'SELECT * FROM `table` LIMIT 5, 15';
        $select = (new Select($this->mockConnection, new NullLogger))->from('table')->limit(15, 5);
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
