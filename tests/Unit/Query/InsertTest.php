<?php

namespace MadeSimple\Database\Tests\Unit\Query;

use MadeSimple\Database\Compiler;
use MadeSimple\Database\CompilerInterface;
use MadeSimple\Database\Query\Insert;
use MadeSimple\Database\Tests\MockConnector;
use MadeSimple\Database\Tests\MockConnection;
use MadeSimple\Database\Tests\TestCase;

class InsertTest extends TestCase
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
     * Test insert into.
     */
    public function testInto()
    {
        $sql    = 'INSERT INTO `table`';
        $insert = (new Insert($this->mockConnection))->into('table');

        $this->assertEquals($sql, $insert->toSql());
    }

    /**
     * Test insert with a single column.
     */
    public function testColumnsSingle()
    {
        $sql    = 'INSERT INTO `table` (`foo`)';
        $insert = (new Insert($this->mockConnection))->into('table')->columns('foo');

        $this->assertEquals($sql, $insert->toSql());
    }

    /**
     * Test insert with multiple columns.
     */
    public function testColumnsMultiple()
    {
        $sql    = 'INSERT INTO `table` (`foo`,`bar`)';
        $insert = (new Insert($this->mockConnection))->into('table')->columns('foo', 'bar');

        $this->assertEquals($sql, $insert->toSql());
    }

    /**
     * Test insert with an array of columns.
     */
    public function testColumnsArray()
    {
        $sql    = 'INSERT INTO `table` (`foo`,`bar`)';
        $insert = (new Insert($this->mockConnection))->into('table')->columns(['foo', 'bar']);

        $this->assertEquals($sql, $insert->toSql());
    }

    /**
     * Test insert with a single value and single row.
     */
    public function testValuesSingleRowSingleValue()
    {
        $sql = 'INSERT INTO `table` (`foo`) VALUES (?)';

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql)->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();

        $insert = (new Insert($this->mockConnection))->into('table')->columns('foo')->values(2);
        $compiledSql        = $insert->toSql();
        list($pdoStatement) = $insert->statement();

        $this->assertEquals($sql, $compiledSql);
        $this->assertEquals($this->mockPdoStatement, $pdoStatement);

    }

    /**
     * Test insert with multiple values and single row.
     */
    public function testValuesSingleRowMultipleValues()
    {
        $sql = 'INSERT INTO `table` (`foo`,`bar`) VALUES (?,?)';

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql)->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(2, 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();

        $insert = (new Insert($this->mockConnection))->into('table')->columns('foo', 'bar')->values(2, 3);
        $compiledSql        = $insert->toSql();
        list($pdoStatement) = $insert->statement();

        $this->assertEquals($sql, $compiledSql);
        $this->assertEquals($this->mockPdoStatement, $pdoStatement);

    }

    /**
     * Test insert with an array of values and single row.
     */
    public function testValuesSingleRowArrayValues()
    {
        $sql = 'INSERT INTO `table` (`foo`,`bar`) VALUES (?,?)';

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql)->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(2, 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();

        $insert = (new Insert($this->mockConnection))->into('table')->columns(['foo', 'bar'])->values([2, 3]);
        $compiledSql        = $insert->toSql();
        list($pdoStatement) = $insert->statement();

        $this->assertEquals($sql, $compiledSql);
        $this->assertEquals($this->mockPdoStatement, $pdoStatement);

    }

    /**
     * Test insert with a single value and single row.
     */
    public function testValuesMultipleRowSingleValue()
    {
        $sql = 'INSERT INTO `table` (`foo`) VALUES (?),(?)';

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql)->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(2, 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $insert = (new Insert($this->mockConnection))->into('table')->columns('foo')->values(2, 3);
        $compiledSql        = $insert->toSql();
        list($pdoStatement) = $insert->statement();

        $this->assertEquals($sql, $compiledSql);
        $this->assertEquals($this->mockPdoStatement, $pdoStatement);

    }

    /**
     * Test insert with multiple values and single row.
     */
    public function testValuesMultipleRowMultipleValues()
    {
        $sql = 'INSERT INTO `table` (`foo`,`bar`) VALUES (?,?),(?,?)';

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql)->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(2, 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(3, 5, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(4, 7, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $insert = (new Insert($this->mockConnection))->into('table')->columns('foo', 'bar')->values(2, 3, 5, 7);
        $compiledSql        = $insert->toSql();
        list($pdoStatement) = $insert->statement();

        $this->assertEquals($sql, $compiledSql);
        $this->assertEquals($this->mockPdoStatement, $pdoStatement);

    }

    /**
     * Test insert with an array of values and single row.
     */
    public function testValuesMultipleRowArrayValues()
    {
        $sql = 'INSERT INTO `table` (`foo`,`bar`) VALUES (?,?),(?,?)';

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql)->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(2, 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(3, 5, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(4, 7, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $insert = (new Insert($this->mockConnection))->into('table')
            ->columns(['foo', 'bar'])
            ->values([2, 3])
            ->values([5, 7]);
        $compiledSql        = $insert->toSql();
        list($pdoStatement) = $insert->statement();

        $this->assertEquals($sql, $compiledSql);
        $this->assertEquals($this->mockPdoStatement, $pdoStatement);

    }
}