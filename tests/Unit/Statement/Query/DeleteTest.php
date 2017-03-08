<?php

namespace Tests\Unit\Query;

use MadeSimple\Database\Statement\Query\Clause;
use MadeSimple\Database\Statement\Query\Delete;
use Tests\MockConnection;
use Tests\TestCase;

class DeleteTest extends TestCase
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
     * Test delete from without alias.
     */
    public function testFromWithoutAlias()
    {
        $sql    = 'DELETE FROM `table`';
        $delete = (new Delete($this->mockConnection))->from('table');

        $this->assertEquals($sql, $delete->toSql());
    }

    /**
     * Test delete from with alias.
     */
    public function testFromWithAlias()
    {
        $sql    = 'DELETE `t` FROM `table` AS `t`';
        $delete = (new Delete($this->mockConnection))->from('table', 't');

        $this->assertEquals($sql, $delete->toSql());
    }

    /**
     * Test set parameter.
     */
    public function testSetParameter()
    {
        $sql = 'DELETE FROM `table` WHERE `foo` = :bar';

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':bar', 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $delete = (new Delete($this->mockConnection))->from('table')->where('foo = :bar')->setParameter('bar', 1);
        $this->assertEquals($this->mockPdoStatement, $delete->execute());
    }

    /**
     * Test set wildcard parameters
     */
    public function testSetParametersWildcard()
    {
        $sql = 'DELETE FROM `table` WHERE `foo` = ?';

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $delete = (new Delete($this->mockConnection))->from('table')->where('foo = ?')->setParameters([1]);
        $this->assertEquals($this->mockPdoStatement, $delete->execute());
    }

    /**
     * Test set named parameters.
     */
    public function testSetParametersNamed()
    {
        $sql = 'DELETE FROM `table` WHERE `foo` = :bar';

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':bar', 1, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $delete = (new Delete($this->mockConnection))->from('table')->where('foo = :bar')->setParameters(['bar' => 1]);
        $this->assertEquals($this->mockPdoStatement, $delete->execute());
    }

    /**
     * Test where without parameters
     */
    public function testWhereWithoutParameters()
    {
        $sql    = 'DELETE FROM `table` WHERE `foo` = 1';
        $delete = (new Delete($this->mockConnection))->from('table')->where('foo = 1');
        $this->assertEquals($sql, $delete->toSql());
    }

    /**
     * Test where with a wildcard parameter
     */
    public function testWhereWithWildcardParameter()
    {
        $sql    = 'DELETE FROM `table` WHERE `foo` = ?';
        $delete = (new Delete($this->mockConnection))->from('table')->where('foo = ?');

        $this->assertEquals($sql, $delete->toSql());
    }

    /**
     * Test where with a named parameter
     */
    public function testWhereWithNamedParameter()
    {
        $sql    = 'DELETE FROM `table` WHERE `foo` = :bar';
        $delete = (new Delete($this->mockConnection))->from('table')->where('foo = :bar');

        $this->assertEquals($sql, $delete->toSql());
    }

    /**
     * Test where with closure.
     */
    public function testWhereWithClosure()
    {
        $sql = 'DELETE FROM `table` WHERE ((`foo` = :foo OR `bar` = :bar) AND `baz` = :qux)';

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':foo', 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':bar', 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(':qux', 5, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $delete = (new Delete($this->mockConnection))
            ->from('table')
            ->where(function (Clause $clause) {
                return $clause
                    ->where(function (Clause $clause) {
                        return $clause
                            ->where('foo = :foo')
                            ->orX('bar = :bar');

                    })
                    ->andX('baz = :qux');
            })
            ->setParameters([
                    'foo' => 2,
                    'bar' => 3,
                    'qux' => 5,
                ]
            );

        $this->assertEquals($sql, $delete->toSql());
        $this->assertEquals($this->mockPdoStatement, $delete->execute());
    }

    /**
     * Test and where with no parameter, wildcard parameter, and named parameter.
     */
    public function testAndWhere()
    {
        $sql    = 'DELETE FROM `table` WHERE `foo` IS TRUE AND `bar` = ? AND (`baz` = :qux)';
        $delete = (new Delete($this->mockConnection))
            ->from('table')
            ->where('foo IS TRUE')
            ->andWhere('bar = ?')
            ->andWhere(function (Clause $clause) {
                return $clause->where('baz = :qux');
            });

        $this->assertEquals($sql, $delete->toSql());
    }

    /**
     * Test or where with no parameter, wildcard parameter, and named parameter.
     */
    public function testOrWhere()
    {
        $sql    = 'DELETE FROM `table` WHERE `foo` IS NULL OR `bar` = ? OR (`baz` = :qux)';
        $delete = (new Delete($this->mockConnection))
            ->from('table')
            ->where('foo IS NULL')
            ->orWhere('bar = ?')
            ->orWhere(function (Clause $clause) {
                return $clause->where('baz = :qux');
            });
        $this->assertEquals($sql, $delete->toSql());
    }
}