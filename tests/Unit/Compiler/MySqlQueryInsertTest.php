<?php

namespace MadeSimple\Database\Tests\Unit\Compiler;

use MadeSimple\Database\Query\Insert;
use MadeSimple\Database\Tests\CompilableMySqlTestCase;

class MySqlQueryInsertTest extends CompilableMySqlTestCase
{
    /**
     * Test insert into.
     */
    public function testQueryInsertInto()
    {
        $sql    = 'INSERT INTO `table`';
        $insert = (new Insert($this->mockConnection))->into('table');

        $this->assertEquals($sql, $insert->toSql());
    }

    /**
     * Test insert with a single column.
     */
    public function testQueryInsertColumnsSingle()
    {
        $sql    = 'INSERT INTO `table` (`foo`)';
        $insert = (new Insert($this->mockConnection))->into('table')->columns('foo');

        $this->assertEquals($sql, $insert->toSql());
    }

    /**
     * Test insert with multiple columns.
     */
    public function testQueryInsertColumnsMultiple()
    {
        $sql    = 'INSERT INTO `table` (`foo`,`bar`)';
        $insert = (new Insert($this->mockConnection))->into('table')->columns('foo', 'bar');

        $this->assertEquals($sql, $insert->toSql());
    }

    /**
     * Test insert with an array of columns.
     */
    public function testQueryInsertColumnsArray()
    {
        $sql    = 'INSERT INTO `table` (`foo`,`bar`)';
        $insert = (new Insert($this->mockConnection))->into('table')->columns(['foo', 'bar']);

        $this->assertEquals($sql, $insert->toSql());
    }

    /**
     * Test insert with a single value and single row.
     */
    public function testQueryInsertValuesSingleRowSingleValue()
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
    public function testQueryInsertValuesSingleRowMultipleValues()
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
    public function testQueryInsertValuesSingleRowArrayValues()
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
    public function testQueryInsertValuesMultipleRowSingleValue()
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
    public function testQueryInsertValuesMultipleRowMultipleValues()
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
    public function testQueryInsertValuesMultipleRowArrayValues()
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