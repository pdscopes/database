<?php

namespace MadeSimple\Database\Tests\Unit\Query;

use MadeSimple\Database\Query\Insert;
use MadeSimple\Database\Tests\CompilableTestCase;

class InsertTest extends CompilableTestCase
{
    /**
     * Test insert into.
     */
    public function testInto()
    {
        $query = (new Insert($this->mockConnection))->into('table');
        $array = $query->toArray();

        $this->assertInstanceOf(Insert::class, $query);
        $this->assertArrayHasKey('into', $array);
        $this->assertArrayHasKey('table', $array['into']);
        $this->assertEquals('table', $array['into']['table']);
    }

    /**
     * Test insert with a single column.
     */
    public function testColumnsSingle()
    {
        $query = (new Insert($this->mockConnection))->columns('field1');
        $array = $query->toArray();

        $this->assertInstanceOf(Insert::class, $query);
        $this->assertArrayHasKey('columns', $array);
        $this->assertEquals(['field1'], $array['columns']);
    }

    /**
     * Test insert with multiple columns.
     */
    public function testColumnsMultiple()
    {
        $query = (new Insert($this->mockConnection))->columns('field1', 'field2');
        $array = $query->toArray();

        $this->assertInstanceOf(Insert::class, $query);
        $this->assertArrayHasKey('columns', $array);
        $this->assertEquals(['field1', 'field2'], $array['columns']);
    }

    /**
     * Test insert with an array of columns.
     */
    public function testColumnsArray()
    {
        $query = (new Insert($this->mockConnection))->columns(['field1', 'field2']);
        $array = $query->toArray();

        $this->assertInstanceOf(Insert::class, $query);
        $this->assertArrayHasKey('columns', $array);
        $this->assertEquals(['field1', 'field2'], $array['columns']);
    }

    /**
     * Test insert with a single value and single row.
     */
    public function testValuesSingleValue()
    {
        $query = (new Insert($this->mockConnection))->values(2);
        $array = $query->toArray();

        $this->assertInstanceOf(Insert::class, $query);
        $this->assertArrayHasKey('values', $array);
        $this->assertEquals([2], $array['values']);
    }

    /**
     * Test insert with multiple values and single row.
     */
    public function testValuesMultipleValues()
    {
        $query = (new Insert($this->mockConnection))->values(2, 3);
        $array = $query->toArray();

        $this->assertInstanceOf(Insert::class, $query);
        $this->assertArrayHasKey('values', $array);
        $this->assertEquals([2, 3], $array['values']);
    }

    /**
     * Test insert with an array of values and single row.
     */
    public function testValuesArrayValues()
    {
        $query = (new Insert($this->mockConnection))->values([2, 3]);
        $array = $query->toArray();

        $this->assertInstanceOf(Insert::class, $query);
        $this->assertArrayHasKey('values', $array);
        $this->assertEquals([2, 3], $array['values']);
    }


    /**
     * Test affectedRows uses the PDOStatement to retrieve row count.
     */
    public function testAffectedRow()
    {
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();
        $this->mockPdoStatement->shouldReceive('rowCount')->once()->withNoArgs()->andReturn(4);
        $this->mockPdo->shouldReceive('prepare')->once()->with('SQL')->andReturn($this->mockPdoStatement);


        $delete = (new Insert($this->mockConnection));
        $delete->query('SQL', []);
        $this->assertEquals(4, $delete->affectedRows());
    }

    /**
     * Test affectedRows uses the PDO to retrieve last insert id.
     */
    public function testLastInsertId()
    {
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();
        $this->mockPdo->shouldReceive('lastInsertId')->once()->with(null)->andReturn(4);
        $this->mockPdo->shouldReceive('prepare')->once()->with('SQL')->andReturn($this->mockPdoStatement);


        $query = (new Insert($this->mockConnection));
        $query->query('SQL', []);
        $this->assertEquals(4, $query->lastInsertId());
    }

    /**
     * Test affectedRows uses the PDO to retrieve last insert id - with name.
     */
    public function testLastInsertIdWithName()
    {
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();
        $this->mockPdo->shouldReceive('lastInsertId')->once()->with('name')->andReturn(4);
        $this->mockPdo->shouldReceive('prepare')->once()->with('SQL')->andReturn($this->mockPdoStatement);


        $query = (new Insert($this->mockConnection));
        $query->query('SQL', []);
        $this->assertEquals(4, $query->lastInsertId('name'));
    }

    /**
     * Test buildSql calls Compiler::compileQueryInsert.
     */
    public function testBuildSql()
    {
        $statement = ['from' => ['table' => 't']];
        $this->mockCompiler->shouldReceive('compileQueryInsert')->once()->with($statement)->andReturn(['SQL', []]);

        $query = (new Insert($this->mockConnection));
        list($sql, $bindings) = $query->buildSql($statement);
        $this->assertEquals('SQL', $sql);
        $this->assertEquals([], $bindings);
    }

    /**
     * Test insert query is tidies properly after execution.
     */
    public function testTidyAfterExecution()
    {
        $this->mockCompiler->shouldReceive('compileQueryInsert')->once()->withAnyArgs()->andReturn(['SQL', []]);
        $this->mockPdo->shouldReceive('prepare')->once()->with('SQL')->andReturn($this->mockPdoStatement);
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();

        $query = (new Insert($this->mockConnection))->into('table')->columns('field')->values('val');
        $array  = $query->toArray();

        $this->assertArrayHasKey('into', $array);
        $this->assertArrayHasKey('columns', $array);
        $this->assertArrayHasKey('values', $array);

        $query->statement();

        $array  = $query->toArray();
        $this->assertArrayHasKey('into', $array);
        $this->assertArrayHasKey('columns', $array);
        $this->assertArrayNotHasKey('values', $array);
    }
}