<?php

namespace Tests\Unit\QueryBuilder;

use MadeSimple\Database\QueryBuilder\Clause;
use MadeSimple\Database\QueryBuilder\Update;
use Tests\MockConnection;
use Tests\TestCase;

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

    protected function setUp()
    {
        parent::setUp();

        $this->mockPdo          = \Mockery::mock(\PDO::class);
        $this->mockPdoStatement = \Mockery::mock(\PDOStatement::class);
        $this->mockConnection   = new MockConnection($this->mockPdo);
    }


    /**
     * Test update table without alias.
     */
    public function testTableWithoutAlias()
    {
        $sql    = "UPDATE `table`\nSET\n`foo` = ?";
        $update = (new Update($this->mockConnection))->table('table')->columns('foo');

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update table with alias.
     */
    public function testTableWithAlias()
    {
        $sql    = "UPDATE `table` AS `t`\nSET\n`foo` = ?";
        $update = (new Update($this->mockConnection))->table('table', 't')->columns('foo');

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update set.
     */
    public function testSet()
    {
        $sql    = "UPDATE `table`\nSET\n`foo` = :foo,\n`bar` = :bar";
        $update = (new Update($this->mockConnection))->table('table')->set('foo', 1)->set('bar', 2);

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update single column.
     */
    public function testColumnsSingle()
    {
        $sql    = "UPDATE `table`\nSET\n`foo` = ?";
        $update = (new Update($this->mockConnection))->table('table')->columns('foo');

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update multiple columns.
     */
    public function testColumnsMultiple()
    {
        $sql    = "UPDATE `table`\nSET\n`foo` = ?,\n`bar` = ?";
        $update = (new Update($this->mockConnection))->table('table')->columns('foo', 'bar');

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update an array of columns.
     */
    public function testColumnsArray()
    {
        $sql    = "UPDATE `table`\nSET\n`foo` = ?,\n`bar` = ?";
        $update = (new Update($this->mockConnection))->table('table')->columns(['foo', 'bar']);

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test update single value.
     */
    public function testValuesSingle()
    {
        $sql    = "UPDATE `table`\nSET\n`foo` = ?";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');
        
        $update = (new Update($this->mockConnection))->table('table')->columns('foo')->values(2);

        $this->assertEquals($sql, $update->toSql());
        $this->assertEquals($this->mockPdoStatement, $update->execute());
    }

    /**
     * Test update single value.
     */
    public function testValuesMultiple()
    {
        $sql    = "UPDATE `table`\nSET\n`foo` = ?,\n`bar` = ?";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(2, 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $update = (new Update($this->mockConnection))->table('table')->columns('foo', 'bar')->values(2, 3);

        $this->assertEquals($sql, $update->toSql());
        $this->assertEquals($this->mockPdoStatement, $update->execute());
    }

    /**
     * Test update an array of columns.
     */
    public function testValuesArray()
    {
        $sql    = "UPDATE `table`\nSET\n`foo` = ?,\n`bar` = ?";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(2, 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $update = (new Update($this->mockConnection))->table('table')->columns(['foo', 'bar'])->values([2, 3]);

        $this->assertEquals($sql, $update->toSql());
        $this->assertEquals($this->mockPdoStatement, $update->execute());
    }

    /**
     * Test set parameter.
     */
    public function testSetParameter()
    {
        $sql = "UPDATE `table`\nSET\n`foo` = :foo";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':foo', 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $update = (new Update($this->mockConnection))->table('table')->set('foo', 2)->setParameter('foo', 1);
        $this->assertEquals($this->mockPdoStatement, $update->execute());
    }

    /**
     * Test set wildcard parameters
     */
    public function testSetParametersWildcard()
    {
        $sql = "UPDATE `table`\nSET\n`foo` = ?";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $update = (new Update($this->mockConnection))->table('table')->columns('foo')->setParameters([1]);
        $this->assertEquals($this->mockPdoStatement, $update->execute());
    }

    /**
     * Test set named parameters.
     */
    public function testSetParametersNamed()
    {
        $sql = "UPDATE `table`\nSET\n`foo` = :foo";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':foo', 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $update = (new Update($this->mockConnection))->table('table')->set('foo', 2)->setParameters(['foo' => 1]);
        $this->assertEquals($this->mockPdoStatement, $update->execute());
    }

    /**
     * Test where without parameters
     */
    public function testWhereWithoutParameters()
    {
        $sql    = "UPDATE `table`\nSET\n`bar` = :bar\nWHERE `foo` IS TRUE";
        $update = (new Update($this->mockConnection))->table('table')->set('bar', 'foo')->where('foo IS TRUE');
        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test where with a wildcard parameter
     */
    public function testWhereWithWildcardParameter()
    {
        $sql    = "UPDATE `table`\nSET\n`bar` = :bar\nWHERE `foo` = ?";
        $update = (new Update($this->mockConnection))->table('table')->set('bar', 'foo')->where('foo = ?');

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test where with a named parameter
     */
    public function testWhereWithNamedParameter()
    {
        $sql    = "UPDATE `table`\nSET\n`bar` = :bar\nWHERE `foo` = :foo";
        $update = (new Update($this->mockConnection))->table('table')->set('bar', 'foo')->where('foo = :foo');

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test where with closure.
     */
    public function testWhereWithClosure()
    {
        $sql = "UPDATE `table`\nSET\n`qux` = :qux\nWHERE ((`foo` = :foo OR `bar` = :bar) AND `baz` = :baz)";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':foo', 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':bar', 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':qux', 5, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $update = (new Update($this->mockConnection))
            ->table('table')
            ->set('qux', 1)
            ->where(function (Clause $clause) {
                return $clause
                    ->where(function (Clause $clause) {
                        return $clause
                            ->where('foo = :foo')
                            ->orX('bar = :bar');

                    })
                    ->andX('baz = :baz');
            })
            ->setParameters([
                    'foo' => 2,
                    'bar' => 3,
                    'qux' => 5,
                ]
            );

        $this->assertEquals($sql, $update->toSql());
        $this->assertEquals($this->mockPdoStatement, $update->execute());
    }

    /**
     * Test and where with no parameter, wildcard parameter, and named parameter.
     */
    public function testAndWhere()
    {
        $sql    = "UPDATE `table`\nSET\n`qux` = :qux\nWHERE `foo` IS TRUE AND `bar` = ? AND (`baz` = :baz)";
        $update = (new Update($this->mockConnection))
            ->table('table')
            ->set('qux', 1)
            ->where('foo IS TRUE')
            ->andWhere('bar = ?')
            ->andWhere(function (Clause $clause) {
                return $clause->where('baz = :baz');
            });

        $this->assertEquals($sql, $update->toSql());
    }

    /**
     * Test or where with no parameter, wildcard parameter, and named parameter.
     */
    public function testOrWhere()
    {
        $sql    = "UPDATE `table`\nSET\n`qux` = :qux\nWHERE `foo` IS NULL OR `bar` = ? OR (`baz` = :baz)";
        $update = (new Update($this->mockConnection))
            ->table('table')
            ->set('qux', 1)
            ->where('foo IS NULL')
            ->orWhere('bar = ?')
            ->orWhere(function (Clause $clause) {
                return $clause->where('baz = :baz');
            });
        $this->assertEquals($sql, $update->toSql());
    }
}