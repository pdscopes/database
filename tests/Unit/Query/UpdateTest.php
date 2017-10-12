<?php

namespace MadeSimple\Database\Tests\Unit\Query;

use MadeSimple\Database\Query\Update;
use MadeSimple\Database\Tests\CompilableTestCase;

class UpdateTest extends CompilableTestCase
{
    /**
     * Test update table.
     */
    public function testTable()
    {
        $query = (new Update($this->mockConnection))->table('table');
        $array = $query->toArray();

        $this->assertInstanceOf(Update::class, $query);
        $this->assertArrayHasKey('table', $array);
        $this->assertEquals('table', $array['table']);
    }

    /**
     * Test update set.
     */
    public function testSet()
    {
        $query = (new Update($this->mockConnection))->set('field', 'val');
        $array = $query->toArray();

        $this->assertInstanceOf(Update::class, $query);
        $this->assertArrayHasKey('set', $array);
        $this->assertEquals(['field' => 'val'], $array['set']);
    }

    /**
     * Test update set array single column.
     */
    public function testSetArraySingle()
    {
        $query = (new Update($this->mockConnection))->set(['field' => 'val']);
        $array = $query->toArray();

        $this->assertInstanceOf(Update::class, $query);
        $this->assertArrayHasKey('set', $array);
        $this->assertEquals(['field' => 'val'], $array['set']);
    }

    /**
     * Test update set array multiple columns.
     */
    public function testSetArrayMultiple()
    {
        $query = (new Update($this->mockConnection))->set(['field1' => 'val1', 'field2' => 'val2']);
        $array = $query->toArray();

        $this->assertInstanceOf(Update::class, $query);
        $this->assertArrayHasKey('set', $array);
        $this->assertEquals(['field1' => 'val1', 'field2' => 'val2'], $array['set']);
    }


    /**
     * Test affectedRows uses the PDOStatement to retrieve row count.
     */
    public function testAffectedRow()
    {
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();
        $this->mockPdoStatement->shouldReceive('rowCount')->once()->withNoArgs()->andReturn(4);
        $this->mockPdo->shouldReceive('prepare')->once()->with('SQL')->andReturn($this->mockPdoStatement);


        $query = (new Update($this->mockConnection));
        $query->query('SQL', []);
        $this->assertEquals(4, $query->affectedRows());
    }

    /**
     * Test buildSql calls Compiler::compileQueryUpdate.
     */
    public function testBuildSql()
    {
        $statement = ['table' => ['table' => 't']];
        $this->mockCompiler->shouldReceive('compileQueryUpdate')->once()->with($statement)->andReturn(['SQL', []]);

        $query = (new Update($this->mockConnection));
        list($sql, $bindings) = $query->buildSql($statement);
        $this->assertEquals('SQL', $sql);
        $this->assertEquals([], $bindings);
    }

    /**
     * Test insert query is tidies properly after execution.
     */
    public function testTidyAfterExecution()
    {
        $this->mockCompiler->shouldReceive('compileQueryUpdate')->once()->withAnyArgs()->andReturn(['SQL', []]);
        $this->mockPdo->shouldReceive('prepare')->once()->with('SQL')->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();

        $query = (new Update($this->mockConnection))->table('table')->set(['field' => 'val']);
        $array  = $query->toArray();

        $this->assertArrayHasKey('table', $array);
        $this->assertArrayHasKey('set', $array);

        $query->statement();

        $array  = $query->toArray();
        $this->assertArrayHasKey('table', $array);
        $this->assertArrayNotHasKey('set', $array);
    }
}