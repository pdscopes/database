<?php

namespace MadeSimple\Database\Tests\Unit\Query;

use MadeSimple\Database\Query\Delete;
use MadeSimple\Database\Tests\CompilableTestCase;

class DeleteTest extends CompilableTestCase
{
    /**
     * Test delete from without alias.
     */
    public function testFromWithoutAlias()
    {
        $query = (new Delete($this->mockConnection))->from('table');
        $array = $query->toArray();

        $this->assertInstanceOf(Delete::class, $query);
        $this->assertArrayHasKey('from', $array);
        $this->assertArrayHasKey('table', $array['from']);
        $this->assertArrayHasKey('alias', $array['from']);
        $this->assertEquals('table', $array['from']['table']);
        $this->assertEquals(null, $array['from']['alias']);
    }

    /**
     * Test delete from with alias.
     */
    public function testFromWithAlias()
    {
        $query = (new Delete($this->mockConnection))->from('table', 't');
        $array = $query->toArray();

        $this->assertInstanceOf(Delete::class, $query);
        $this->assertArrayHasKey('from', $array);
        $this->assertArrayHasKey('table', $array['from']);
        $this->assertArrayHasKey('alias', $array['from']);
        $this->assertEquals('table', $array['from']['table']);
        $this->assertEquals('t', $array['from']['alias']);
    }


    /**
     * Test affectedRows uses the PDOStatement to retrieve row count.
     */
    public function testAffectedRow()
    {
        $this->mockPdoStatement->shouldReceive('execute')->once()->withNoArgs();
        $this->mockPdoStatement->shouldReceive('rowCount')->once()->withNoArgs()->andReturn(4);
        $this->mockPdo->shouldReceive('prepare')->once()->with('SQL')->andReturn($this->mockPdoStatement);


        $query = (new Delete($this->mockConnection));
        $query->query('SQL', []);
        $this->assertEquals(4, $query->affectedRows());
    }

    /**
     * Test buildSql calls Compiler::compileQueryDelete.
     */
    public function testBuildSql()
    {
        $statement = ['from' => ['table' => 't']];
        $this->mockCompiler->shouldReceive('compileQueryDelete')->once()->with($statement)->andReturn(['SQL', []]);

        $query = (new Delete($this->mockConnection));
        list($sql, $bindings) = $query->buildSql($statement);
        $this->assertEquals('SQL', $sql);
        $this->assertEquals([], $bindings);
    }
}