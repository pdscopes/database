<?php

namespace Tests\Unit\Query;

use MadeSimple\Database\Statement\Query\Insert;
use Tests\MockConnection;
use Tests\TestCase;

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

    protected function setUp()
    {
        parent::setUp();

        $this->mockPdo          = \Mockery::mock(\PDO::class);
        $this->mockPdoStatement = \Mockery::mock(\PDOStatement::class);
        $this->mockConnection   = new MockConnection($this->mockPdo);
    }


    /**
     * Test insert into.
     */
    public function testInto()
    {
        $sql    = "INSERT INTO `table`";
        $insert = (new Insert($this->mockConnection))->into('table');

        $this->assertEquals($sql, $insert->toSql());
    }

    /**
     * Test insert with a single column.
     */
    public function testColumnsSingle()
    {
        $sql    = "INSERT INTO `table`\n(`foo`)";
        $insert = (new Insert($this->mockConnection))->into('table')->columns('foo');

        $this->assertEquals($sql, $insert->toSql());
    }

    /**
     * Test insert with multiple columns.
     */
    public function testColumnsMultiple()
    {
        $sql    = "INSERT INTO `table`\n(`foo`,`bar`)";
        $insert = (new Insert($this->mockConnection))->into('table')->columns('foo', 'bar');

        $this->assertEquals($sql, $insert->toSql());
    }

    /**
     * Test insert with an array of columns.
     */
    public function testColumnsArray()
    {
        $sql    = "INSERT INTO `table`\n(`foo`,`bar`)";
        $insert = (new Insert($this->mockConnection))->into('table')->columns(['foo', 'bar']);

        $this->assertEquals($sql, $insert->toSql());
    }

    /**
     * Test insert with a single value and single row.
     */
    public function testValuesSingleRowSingleValue()
    {
        $sql = "INSERT INTO `table`\n(`foo`)\nVALUES\n(?)";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $insert = (new Insert($this->mockConnection))->into('table')->columns('foo')->values(2);

        $this->assertEquals($sql, $insert->toSql());
        $this->assertEquals($this->mockPdoStatement, $insert->execute());

    }

    /**
     * Test insert with multiple values and single row.
     */
    public function testValuesSingleRowMultipleValues()
    {
        $sql = "INSERT INTO `table`\n(`foo`,`bar`)\nVALUES\n(?,?)";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(2, 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $insert = (new Insert($this->mockConnection))->into('table')->columns('foo', 'bar')->values(2, 3);

        $this->assertEquals($sql, $insert->toSql());
        $this->assertEquals($this->mockPdoStatement, $insert->execute());

    }

    /**
     * Test insert with an array of values and single row.
     */
    public function testValuesSingleRowArrayValues()
    {
        $sql = "INSERT INTO `table`\n(`foo`,`bar`)\nVALUES\n(?,?)";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(2, 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $insert = (new Insert($this->mockConnection))->into('table')->columns(['foo', 'bar'])->values([2, 3]);

        $this->assertEquals($sql, $insert->toSql());
        $this->assertEquals($this->mockPdoStatement, $insert->execute());

    }

    /**
     * Test insert with a single value and single row.
     */
    public function testValuesMultipleRowSingleValue()
    {
        $sql = "INSERT INTO `table`\n(`foo`)\nVALUES\n(?),\n(?)";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(2, 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $insert = (new Insert($this->mockConnection))->into('table')->columns('foo')->values(2, 3);

        $this->assertEquals($sql, $insert->toSql());
        $this->assertEquals($this->mockPdoStatement, $insert->execute());

    }

    /**
     * Test insert with multiple values and single row.
     */
    public function testValuesMultipleRowMultipleValues()
    {
        $sql = "INSERT INTO `table`\n(`foo`,`bar`)\nVALUES\n(?,?),\n(?,?)";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(2, 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(3, 5, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(4, 7, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $insert = (new Insert($this->mockConnection))->into('table')->columns('foo', 'bar')->values(2, 3, 5, 7);

        $this->assertEquals($sql, $insert->toSql());
        $this->assertEquals($this->mockPdoStatement, $insert->execute());

    }

    /**
     * Test insert with an array of values and single row.
     */
    public function testValuesMultipleRowArrayValues()
    {
        $sql = "INSERT INTO `table`\n(`foo`,`bar`)\nVALUES\n(?,?),\n(?,?)";

        $this->mockPdo->shouldReceive('prepare')->once()->with($sql, [])->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(1, 2, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(2, 3, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(3, 5, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('bindValue')->once()->with(4, 7, \PDO::PARAM_INT)->andReturn(true);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs()->andReturn('statement');

        $insert = (new Insert($this->mockConnection))->into('table')->columns(['foo', 'bar'])->values([2, 3], [5, 7]);

        $this->assertEquals($sql, $insert->toSql());
        $this->assertEquals($this->mockPdoStatement, $insert->execute());

    }
}